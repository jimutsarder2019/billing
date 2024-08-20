<?php

namespace App\Http\Controllers\sms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SmsApi;
use App\Models\SmsTemplates;
use Illuminate\Support\Facades\DB;

class SmsTamplateController extends Controller
{

    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new SmsTemplates();
        $this->modelName = "SmsTemplates";
        $this->routename = "sms_templates.index";
        $this->table = "sms_templates";
        $this->tamplate = "content.sms";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('SMS Template')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $sms_templates = $this->model->with('sms_api')->latest()->paginate($request->item ?? 10);
            return view("content.sms.sms-template", compact('sms_templates'));
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
    public function create()
    {
        $sms_apis = SmsApi::get();
        return view("content.sms.smsTemplateCreateUpdate", compact('sms_apis'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('SMS Template Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required|unique:sms_templates,name',
            'api' => 'required',
            'template_for' => 'required',
            'template' => 'required'
        ]);
        try {
            SmsTemplates::create([
                'name' => $request->name,
                'sms_apis_id' => $request->api,
                'type' => $request->template_for,
                'template' => $request->template
            ]);
            notify()->success("$this->modelName Create Successfully");
            return redirect()->route("$this->routename");
        } catch (\Throwable $th) {
             notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $this->model->find($id)->update(['status' => DB::raw("IF(status = 1, 0 ,1)")]);
            notify()->success("$this->modelName Delete Successfully");
            return back();
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
            if ($data) {
                $sms_apis = SmsApi::get();
                return view("content.sms.smsTemplateCreateUpdate", compact('data', 'sms_apis'));
            } else {
                return abort(404);
            }
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
        if (!auth()->user()->can('SMS Template Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

        $request->validate([
            'name' => "required|unique:$this->table,name, $id",
            'api' => 'required',
            'template_for' => 'required',
            'template' => 'required'
        ]);
        $request->validate(['name' => "required|unique:$this->table,name, $id"]);
        try {
            $this->model->find($id)->update([
                'name' => $request->name,
                'sms_apis_id' => $request->api,
                'type' => $request->template_for,
                'template' => $request->template
            ]);
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
        if (!auth()->user()->can('SMS Template Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

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
