<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DivisionController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new Division();
        $this->modelName = "Division";
        $this->routename = "division.index";
        $this->table = "divisions";
        $this->tamplate = "content.division";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('Divisions')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = $this->model->select('id', 'name')->latest()->paginate($request->item ?? 10);
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
        if (!auth()->user()->can('Divisions Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
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
        if (!auth()->user()->can('Divisions Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
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
        if (!auth()->user()->can('Divisions Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data  = $this->model->with('district')->find($id);
            
            if ($data->district && $data->district->count() > 0) return error_message('data cannot be delete');
            $data->delete();
            notify()->success("$this->modelName Delete Successfully");
            return back();
        } catch (\Throwable $th) {
             notify()->warning($th->getMessage());
            return back();
        }
    }
}
