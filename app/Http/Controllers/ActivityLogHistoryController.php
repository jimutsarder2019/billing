<?php

namespace App\Http\Controllers;

use App\Exports\ActivityLogExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class ActivityLogHistoryController extends Controller
{
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

            // Now you can query your database to get data for the previous month
            // $previousMonthData = Activity::whereBetween('created_at', [
            //     $previousMonth->startOfMonth(), // First day of the previous month
            //     $previousMonth->endOfMonth(),   // Last day of the previous month
            // ])->get();

            $previousMonthData = Activity::where('created_at', '<', $previousMonth->startOfMonth())->get();
            if ($previousMonthData->count() > 0) {
                $filePath = 'backup/activity'; // Define your desired public path
                $fileName = 'activity-log-' . Date::now()->format('d-M-Y H:i:s a') . ".csv"; // Define your desired file name

                $arraydata = [
                    [
                        'causer',
                        'event',
                        'subject',
                        'properties',
                    ], // Header row
                ];
                foreach ($previousMonthData as $item) {
                    $causerModel = $item->causer_id !== null ? app($item->causer_type)->where('id', $item->causer_id)->first() : null;
                    $subjectModel = $item->subject_id  !== null ? app($item->subject_type)->where('id', $item->subject_id)->first() : null;
                    $arr = [
                        $causerModel ? $causerModel['name'] : '',
                        $item->event,
                        $subjectModel ? class_basename($subjectModel) : '', // Assuming full_name is a column in your Activity model
                        $item->properties,
                    ];
                    $arraydata[] = $arr;
                }
                $customData = new Collection($arraydata);

                // Save the file to the public path
                Excel::store(new ActivityLogExport($customData), $filePath . '/' . $fileName, 'public');

                // Create symbolic link
                Artisan::call('storage:link');
                $previousMonthData = Activity::where('created_at', '<', $previousMonth->startOfMonth())->delete();
            }

            $directoryPath = public_path('storage/backup/activity');
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
            if (!auth()->user()->can('Activity Log Auth')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data = Activity::latest()->where('causer_id', $request->manager ? $request->manager : auth()->user()->id)->paginate($request->item ?? 10);
            return view("content.activity.index", compact('data', 'get_files'));
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
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('Activity Log All')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data = Activity::latest()->paginate($request->item ?? 10);
            return view("content.activity.index", compact('data'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function doanload_file(Request $request)
    {
        try {
            if ($request->action == 'delete') {
                // if (file_exists(public_path("storage/backup/activity/$file")))
                Storage::delete("$request->file");
                notify()->success("Delete Susccessfully");
                Artisan::call('storage:link');
                return back();
            } else {
                return response()->download(public_path("$request->file"));
            }
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
}
