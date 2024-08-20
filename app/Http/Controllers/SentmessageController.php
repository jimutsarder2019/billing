<?php

namespace App\Http\Controllers;

use App\Exports\SMSExport;
use App\Models\AdminSetting;
use App\Models\Sentmessage;
use App\Models\SmsApi;
use App\Services\Message\MessageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class SentmessageController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new Sentmessage();
        $this->modelName = "Sentmessage";
        $this->routename = "division.index";
        $this->table = "sentmessages";
        $this->tamplate = "content.sendsms";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Get the current date
            $currentDate = Carbon::now();
            // Subtract one month to get the previous month
            $previousMonth = $currentDate->subMonth();


            $previousMonthData = $this->model->where('created_at', '<', $previousMonth->startOfMonth())->get();
            if ($previousMonthData->count() > 0) {
                $filePath = 'backup/sms'; // Define your desired public path
                $fileName = 'sms-report-' . Date::now()->format('d-M-Y H:i:s a') . ".csv"; // Define your desired file name

                $arraydata = [
                    [
                        '#ID',
                        'message id',
                        'number',
                        'message',
                        'status',
                        'created_at',
                    ], // Header row
                ];
                foreach ($previousMonthData as $item) {
                    $arr = [
                        $item->id,
                        $item->message_id,
                        $item->number,
                        $item->message,
                        $item->status,
                        $item->created_at->format('d-M-Y H:i:s A'),
                    ];
                    $arraydata[] = $arr;
                }
                $customData = new Collection($arraydata);

                // Save the file to the public path
                Excel::store(new SMSExport($customData), $filePath . '/' . $fileName, 'public');

                // Create symbolic link
                Artisan::call('storage:link');
                $previousMonthData = $this->model->where('created_at', '<', $previousMonth->startOfMonth())->delete();
            }

            $directoryPath = public_path('storage/backup/sms');
            if (is_dir($directoryPath)) {
                $items = File::files($directoryPath);
                $get_files = [];
                foreach ($items as $item) {
                    $get_files[] = ['name' => $item->getFilename(), 'size' => number_format(($item->getSize() / 1024), 2) . 'kb'];
                }
            } else {
                // Handle the case where the directory does not exist
                $get_files = [];
                // You can add an error message or any other appropriate handling here
            }
            // dd($get_files);

            $data = $this->model->select('id', 'number', 'message_id', 'sms_apis_id', 'sms_templates_id', 'users_id', 'message', 'status', 'status_code', 'created_at')
                ->when($request->search_query, function ($q) use ($request) {
                    $searchQuery = '%' . $request->search_query . '%';
                    return $q->where('number', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('message', 'LIKE', $searchQuery);
                })->latest()->paginate($request->item ?? 10);
            return view("$this->tamplate.index", compact('data', 'get_files'));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("$this->tamplate.addEdit");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['name' => "required|unique:$this->table,name"]);
        try {
            $this->model->create(['name' => $request->name]);
            notify()->success("$this->modelName Create Successfully");
            return redirect()->route("$this->routename");
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $data = $this->model->find($id);
            if (!$data) return abort(404);
            return view("$this->tamplate.addEdit", compact('data'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate(['name' => "required|unique:$this->table,name, $id"]);
        try {
            $this->model->find($id)->update(['name' => $request->name,]);
            notify()->success("$this->modelName Update Successfully");
            return redirect()->route("$this->routename");
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->model->find($id)->delete();
            notify()->success("$this->modelName Delete Successfully");
            return back();
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
}
