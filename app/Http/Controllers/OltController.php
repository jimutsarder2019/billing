<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\OLT;
use App\Models\SubZone;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OltController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new OLT();
        $this->modelName = "Division";
        $this->routename = "olt.index";
        $this->table = "o_l_t_s";
        $this->tamplate = "content.network";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if(check_permission('OLT')) return back();
            $data = $this->model->latest()->with('zone', 'sub_zone')->paginate($request->item ?? 10);
            return view("$this->tamplate.view-olt", compact('data'));
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
        if(check_permission('OLT Add')) return back();
        $zones = Zone::get();
        $sub_zones = SubZone::get();
        return view('content.network.add-olt', compact('zones', 'sub_zones'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(check_permission('OLT Add')) return back();
        $request->validate([
            'name' => "required|unique:$this->table,name",
            'zone_id' => 'required',
            'type' => 'required',
            'pon' => 'required',
            'olt_ip' => 'required',
            'total_onu' => 'required',
            'mac' => 'required',
            ]);
            try {
                
                OLT::create([
                    'name' => $request->name,
                    'zone_id' => $request->zone_id,
                    'sub_zone_id' => $request->sub_zone_id,
                    'type' => $request->type,
                    'non_of_pon_port' => $request->pon,
                    'management_ip' => $request->management_ip,
                    'total_onu' => $request->total_onu,
                    'mac' => $request->mac,
                    'olt_ip' => $request->olt_ip,
                    'management_vlan_id' => $request->vlan_id,
                    'management_vlan_ip' => $request->vlan_ip
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function olt_no_of_pon_port($id)
    {
        try {
            $data = $this->model->find($id);
            if (!$data) return error_message('data not fount');
            $zone = $data->zone;
            return response()->json(['no_of_pon_port' => $data->non_of_pon_port, 'zone' => $zone]);
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
        if(check_permission('OLT Edit')) return back();
        try {
            $data = $this->model->find($id);
            if ($data) {
                $zones = Zone::get();
                $sub_zones = SubZone::get();
                return view('content.network.add-olt', compact('data', 'zones', 'sub_zones'));
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
        if(check_permission('OLT Edit')) return back();
        $request->validate([
            'name' => "required|unique:$this->table,name, $id",
            'zone_id' => 'required',
            'type' => 'required',
            'olt_ip' => 'required',
            'pon' => 'required',
            'total_onu' => 'required',
            'vlan_id' => 'required',
            ]);
            try {
                $this->model->find($id)->update([
                    'name' => $request->name,
                    'zone_id' => $request->zone_id,
                    'sub_zone_id' => $request->sub_zone_id,
                    'type' => $request->type,
                    'non_of_pon_port' => $request->pon,
                    'management_ip' => $request->management_ip,
                    'total_onu' => $request->total_onu,
                    'mac' => $request->mac,
                    'olt_ip' => $request->olt_ip,
                    'management_vlan_id' => $request->vlan_id,
                    'management_vlan_ip' => $request->vlan_ip
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
                if(check_permission('OLT Delete')) return back();
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
