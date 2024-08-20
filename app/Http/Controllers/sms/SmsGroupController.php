<?php

namespace App\Http\Controllers\sms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Manager;
use App\Models\SmsGroup;
use App\Models\SmsGroupUsers;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SmsGroupController extends Controller
{

    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new SmsGroup();
        $this->modelName = "District";
        $this->routename = "sms-group.index";  // must be lowercase 
        $this->table = "sms_groups"; // must be lowercase 
        $this->tamplate = "content.sms.group";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $data = $this->model->latest()->paginate($request->item ?? 10);
            return view("$this->tamplate.index", compact('data'));
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
    public function create(Request $request)
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
        if ($request->confirm) {
            $users = $request->users;
            if (is_null($request->users)) {
                $user_list = $request->user_type == 'dashboard_user' ? User::select('id', 'name', 'phone')->get() : Manager::select('id', 'name', 'phone')->get();
                $data = ['name' => $request->input('name'), 'user_type' => $request->input('user_type'),];
                notify()->error("Please Select User");
                return redirect()->back()->with('user_list', $user_list)->withInput($data);
            }

            $request->validate([
                'name' => 'required',
                'user_type' => 'required',
                // 'users' =>  'required|array|min:1',
            ]);
            DB::beginTransaction();
            try {
                $group = SmsGroup::create(['name' => $request->name,    'group_type' => $request->user_type,]);
                // 'corporate_user','dashboard_user','active_customer','inactive_customer','pending_customer'
                if ($request->user_type == 'dashboard_user') {
                    foreach ($users as $value) {
                        SmsGroupUsers::create(['smsgroup_id' => $group->id,    'manager_id' => $value]);
                    }
                } else {
                    foreach ($users as $value) {
                        SmsGroupUsers::create(['smsgroup_id' => $group->id,    'customer_id' => $value]);
                    }
                }
                DB::commit();
                notify()->success("$this->modelName Create Successfully");
                return redirect()->route("$this->routename");
            } catch (\Throwable $th) {
                DB::rollBack();
                 notify()->warning($th->getMessage());
            return back();
            }
        } else {
            $user_list = $request->user_type == 'dashboard_user' ? User::select('id', 'name', 'phone')->get() : Manager::select('id', 'name', 'phone')->get();
            $data = ['name' => $request->input('name'), 'user_type' => $request->input('user_type'),];
            return redirect()->back()->with('user_list', $user_list)->withInput($data);
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
            $data = $this->model->with('SmsGroupUsers')->find($id);
            // dd($data);
            if ($data) {
                $data = ['id' => $data->id, 'name' => $data->name, 'user_type' => $data->group_type];
                return view("$this->tamplate.addEdit", compact('data'));
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
        // dd($request->all());
        $request->validate([
            'name' => "required|unique:$this->table,name,$id",
            'division' => 'required'
        ]);
        try {
            $this->model->find($id)->update(['name' => $request->name, 'division_id' => $request->division]);
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
