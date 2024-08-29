<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\OLT;
use App\Models\ONU;
use App\Models\SubZone;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnuController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new ONU();
        $this->modelName = "ONU";
        $this->routename = "onu.index";
        $this->table = "o_n_u_s";
        $this->tamplate = "content.network";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (check_permission('ONU')) return back();
        try {
            $data = $this->model->latest()->with('zone', 'sub_zone')->paginate($request->item ?? 10);
            return view("$this->tamplate.view-onu", compact('data'));
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
        if (check_permission('ONU Add')) return back();
        try {

            $olts = OLT::get();
            $users = Customer::get();
            return view('content.network.add-onu', compact('olts', 'users'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (check_permission('ONU Add')) return back();
        $request->validate([
            'name' => "required|unique:$this->table,name",
            'mac' => 'required',
            'olt_id' => 'required',
            'pon_port' => 'required',
            'onu_id' => 'required',
            'rx_power' => 'required',
            'distance' => 'required',
            'zone_id' => 'required',
        ]);
        if ($request->vlan_tagged != null) {
            $request->validate(['vlan_id' => 'required']);
            $vlan_tagged = true;
        } else {
            $vlan_tagged = false;
        }

        try {


            $this->model->create([
                'name' => $request->name,
                'mac' => $request->mac,
                'olt_id' => $request->olt_id,
                'pon_port' => $request->pon_port,
                'onu_id' => $request->onu_id,
                'rx_power' => $request->rx_power,
                'distance' => $request->distance,
                'customer_id' => $request->user_id,
                'zone_id' => $request->zone_id,
                'vlan_tagged' => $vlan_tagged,
                'vlan_id' => $request->vlan_id,
                'status' => STATUS_TRUE
            ]);
            notify()->success("$this->modelName Create Successfully");
            return redirect()->route("$this->routename");
        } catch (\Throwable $th) {
            dd($th);
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
        if (check_permission('ONU Edit')) return back();
        try {
            $data = $this->model->with('olt')->find($id);
            if ($data) {
                $olts = OLT::get();
                $users = Customer::get();
                $zones = Zone::get();
                $sub_zones = SubZone::get();
                return view('content.network.add-onu', compact('data', 'zones', 'sub_zones', 'olts', 'users'));
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
        if (check_permission('ONU Edit')) return back();
        $request->validate([
            'name' => "required|unique:$this->table,name, $id",
            'mac' => 'required',
            'olt_id' => 'required',
            'pon_port' => 'required',
            'onu_id' => 'required',
            'rx_power' => 'required',
            'distance' => 'required',
            'zone_id' => 'required',
        ]);
        if ($request->vlan_tagged != null) {
            $request->validate([
                'vlan_id' => 'required'
            ]);
            $vlan_tagged = true;
        } else {
            $vlan_tagged = false;
        }
        try {

            $this->model->find($id)->update([
                'name' => $request->name,
                'mac' => $request->mac,
                'olt_id' => $request->olt_id,
                'pon_port' => $request->pon_port,
                'onu_id' => $request->onu_id,
                'rx_power' => $request->rx_power,
                'distance' => $request->distance,
                'customer_id' => $request->user_id,
                'zone_id' => $request->zone_id,
                'vlan_tagged' => $vlan_tagged,
                'vlan_id' => $vlan_tagged == true ? $request->vlan_id : null,
            ]);
            notify()->success("$this->modelName Update Successfully");
            return redirect()->route("$this->routename");
        } catch (\Throwable $th) {
            dd($th);
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
        if (check_permission('ONU Edit')) return back();
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
