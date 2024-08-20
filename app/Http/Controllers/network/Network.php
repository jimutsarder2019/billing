<?php

namespace App\Http\Controllers\network;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ManagerAssignSubZone;
use App\Models\ManagerAssignZone;
use App\Models\Zone as ZoneModel;
use App\Models\SubZone;
use App\Models\OLT;
use App\Models\ONU;
use App\Models\Upazila;
use App\Models\User;

class Network extends Controller
{

    //========= get all zonewise permissions =========
    public function get_zone_wise_subzone(Request $request)
    {

        $subzone = SubZone::select('id', 'name', 'zone_id')->whereIn('zone_id', $request->zone_ids)->get();
        return response()->json($subzone);
    }
    //========= Add New Zone =========
    public function addZone()
    {
        if (!auth()->user()->can('Zone Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $upazila = Upazila::get();
        return view('content.network.add-zone', compact('upazila'));
    }

    //========= Store Zone =========
    public function storeZone(Request $request)
    {
        if (!auth()->user()->can('Zone Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
            // 'abbr' => 'required',
            'upazila' => 'required'
        ]);

        ZoneModel::create([
            'name' => $request->name,
            'abbreviation' => $request->abbr,
            'upazila_id' => $request->upazila,

        ]);
        notify()->success('Create Successfully');
        return redirect()->route('network-view-zone');
    }

    //========= View Zone =========
    public function viewZone()
    {
        if (!auth()->user()->can('Zone')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $zones = ZoneModel::with('sub_zone', 'customer')->when(auth(), function ($q) {
            if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                $assingZone_ids = ManagerAssignZone::where('manager_id', auth()->user()->id)->get()->pluck('zone_id');
                //  dd($assingZone_ids);
                return $q->whereIn('id', $assingZone_ids);
            }
        })->latest()->paginate();
        return view('content.network.view-zone', compact('zones'));
    }

    //========= Edit Zone =========
    public function editZone($id)
    {
        if (!auth()->user()->can('Zone Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $upazila = Upazila::get();
        $zone = ZoneModel::where('id', $id)->first();
        return view('content.network.edit-zone', compact('zone', 'upazila'));
    }



    //========= Delete Zone =========
    public function deleteZone($id)
    {
        if (!auth()->user()->can('Zone Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = ZoneModel::with('sub_zone', 'manager', 'customer')->find($id);
            if ($data->sub_zone->count() > 0 | $data->customer->count() > 0 | $data->manager->count() > 0) return error_message('Please Delete sub Subzone');
            $data->delete();
            notify()->success('Create Successfully');
            return back();
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }


    //========= updateZone =========
    public function updateZone(Request $request, $id)
    {
        if (!auth()->user()->can('Zone Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
        ]);
        $zone = ZoneModel::find($id);
        $zone->name = $request->name;
        $zone->abbreviation = $request->abbr;
        $zone->save();
        notify()->success('Zone Update Successfully');
        return redirect()->route('network-view-zone');
    }

    //========= Add Subzone =========
    public function addSubZone()
    {
        if (!auth()->user()->can('Sub-Zone Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $zones = ZoneModel::where('status', true)->get();
        return view('content.network.add-sub-zone', compact('zones'));
    }

    //========= Store Subzone =========
    public function storeSubZone(Request $request)
    {
        if (!auth()->user()->can('Sub-Zone Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => "required|unique:sub_zones,name",
            'zone_id' => 'required',
        ]);

        SubZone::create([
            'name' => $request->name,
            'zone_id' => $request->zone_id,
        ]);

        notify()->success('Create Successfully');
        return redirect()->route('network-view-sub-zone');
    }

    //========= View Subzone =========
    public function viewSubZone()
    {
        if (!auth()->user()->can('Sub-Zone')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $sub_zones = SubZone::with('customer')->when(auth(), function ($q) {
            if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                $assingsubZone_ids = ManagerAssignSubZone::where('manager_id', auth()->user()->id)->get()->pluck('subzone_id');
                //  dd($assingsubZone_ids);
                return $q->whereIn('id', $assingsubZone_ids);
            }
        })->latest()->paginate();
        return view('content.network.view-sub-zone', compact('sub_zones'));
    }

    //========= Edit Subzone =========
    public function editSubZone($id)
    {
        if (!auth()->user()->can('Sub-Zone Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $sub_zone = SubZone::where('id', $id)->first();
        $zones = ZoneModel::latest()->paginate();
        return view('content.network.edit-sub-zone', compact('sub_zone', 'zones'));
    }

    //========= Update Subzone =========
    public function updateSubZone(Request $request, $id)
    {
        if (!auth()->user()->can('Sub-Zone Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => "required|unique:sub_zones,name, $id",
            'zone_id' => 'required',
        ]);

        $sub_zone = SubZone::find($id);
        $sub_zone->name = $request->name;
        $sub_zone->zone_id = $request->zone_id;
        $sub_zone->save();
        notify()->success('Update Successfully');
        return redirect()->route('network-view-sub-zone');
    }

    //========= Add OLT =========
    public function addOLT()
    {
        if (!auth()->user()->can('OLT Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $zones = ZoneModel::all();
        $sub_zones = SubZone::all();
        return view('content.network.add-olt', compact('zones', 'sub_zones'));
    }

    //========= Store OLT =========
    public function storeOLT(Request $request)
    {
        if (!auth()->user()->can('OLT Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
            'zone_id' => 'required',
            'sub_zone_id' => 'required',
            'type' => 'required',
            'pon' => 'required',
            'management_ip' => 'required',
            'total_onu' => 'required',
            'vlan_id' => 'required',
            'vlan_ip' => 'required',
        ]);

        OLT::create([
            'name' => $request->name,
            'zone_id' => $request->zone_id,
            'sub_zone_id' => $request->sub_zone_id,
            'type' => $request->type,
            'non_of_pon_port' => $request->pon,
            'management_ip' => $request->management_ip,
            'total_onu' => $request->total_onu,
            'management_vlan_id' => $request->vlan_id,
            'management_vlan_ip' => $request->vlan_ip
        ]);
        notify()->success('OLT Create Successfylly');
        return back();
    }
    //========= Get Zone wise Subzone =========
    function get_zonewise_subzone($id)
    {
        $data = SubZone::select('id', 'zone_id', 'name')->where('zone_id', $id)->get();
        return response()->json(['subzone' => $data]);
    }

    //========= Add Onue =========
    public function addONU()
    {
        if (!auth()->user()->can('ONU Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $olts = OLT::all();
        $users = User::all();
        return view('content.network.add-onu', compact('olts', 'users'));
    }

    //========= OLT Details For Add Onu =========
    public function oltDetailsForAddOnu($id)
    {
        $olt = OLT::find($id);
        $zone = $olt->zone->name;
        return response()->json(['olt' => $olt, 'zone' => $zone]);
    }

    //========= Store Onu =========
    public function storeOnu(Request $request)
    {
        if (!auth()->user()->can('ONU Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
            'mac' => 'required',
            'olt_id' => 'required',
            'pon_port' => 'required',
            'onu_id' => 'required',
            'rx_power' => 'required',
            'distance' => 'required',
            // 'user_id' => 'required',
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

        $zone = ZoneModel::where('name', $request->zone_id)->first();
        ONU::create([
            'name' => $request->name,
            'mac' => $request->mac,
            'olt_id' => $request->olt_id,
            'pon_port' => $request->pon_port,
            'onu_id' => $request->onu_id,
            'rx_power' => $request->rx_power,
            'distance' => $request->distance,
            'user_id' => $request->user_id,
            'zone_id' => $zone->id,
            'vlan_tagged' => $vlan_tagged,
            'vlan_id' => $request->vlan_id,
            'status' => 1
        ]);
        notify()->success('ONU Create Successfylly');
        return back();
    }

    /*  
    |
    | ========= Network Delete Sub Zone =========
    | 
    */

    function network_delete_sub_zone($id)
    {
        try {
            $data = SubZone::with('customer', 'manager')->find($id);
            if ($data->customer->count() > 0 | $data->manager->count() > 0) return error_message('data cannot be delete');
            $data->delete();
            notify()->success('Delete Successfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
