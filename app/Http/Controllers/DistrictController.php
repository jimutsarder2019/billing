<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new District();
        $this->modelName = "District";
        $this->routename = "district.index";  // must be lowercase 
        $this->table = "districts"; // must be lowercase 
        $this->tamplate = "content.district";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('Districts')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

        try {
            $data = $this->model->select('id', 'name', 'division_id')->with('division')->latest()->paginate($request->item ?? 10);
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
        $division = Division::select('id', 'name')->get();
        return view("$this->tamplate.addEdit", compact('division'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('Districts Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate(['name' => "required|unique:$this->table,name", 'division' => 'required']);
        try {
            $this->model->create(['name' => $request->name, 'division_id' => $request->division]);
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
                $division = Division::select('id', 'name')->get();
                return view("$this->tamplate.addEdit", compact('data', 'division'));
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
        if (!auth()->user()->can('Districts Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
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
        if (!auth()->user()->can('Districts Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data  = $this->model->with('upazila')->find($id);
            if ($data->upazila->count() > 0) return error_message('data cannot be delete');
            $data->delete();
            notify()->success("$this->modelName Delete Successfully");
            return back();
        } catch (\Throwable $th) {
             notify()->warning($th->getMessage());
            return back();
        }
    }
}
