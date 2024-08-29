<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Upazila;
use Illuminate\Http\Request;

class UpazilaController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new Upazila();
        $this->modelName = "Upazila";
        $this->routename = "thana.index";
        $this->table = "upazilas";
        $this->tamplate = "content.upazila";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('Thana')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = $this->model->select('id', 'name', 'district_id')->with('district')->latest()->paginate($request->item ?? 10);
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
    public function create()
    {
        $district = District::select('id', 'name')->get();
        return view("$this->tamplate.addEdit", compact('district'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('Thana Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate(['name' => "required|unique:$this->table,name", 'district' => 'required']);
        try {
            $this->model->create(['name' => $request->name, 'district_id' => $request->district]);
            notify()->success("Thana Create Successfully");
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
            notify()->success("Thana Delete Successfully");
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
                $district = District::select('id', 'name')->get();
                return view("$this->tamplate.addEdit", compact('data', 'district'));
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
        if (!auth()->user()->can('Thana Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate(['name' => "required|unique:$this->table,name,$id", 'district' => 'required']);
        try {
            $this->model->find($id)->update(['name' => $request->name, 'district_id' => $request->district]);
            notify()->success("Thana Update Successfully");
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
        if (!auth()->user()->can('Thana Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data  = $this->model->with('manager', 'customer', 'sub_zone')->find($id);
            if ($data->upazila->count() > 0 | $data->customer->count() > 0 | $data->sub_zone->count() > 0) return error_message('data cannot be delete');
            $data->delete();
            notify()->success("Thana Delete Successfully");
            return back();
        } catch (\Throwable $th) {
             notify()->warning($th->getMessage());
            return back();
        }
    }
}
