@extends('layouts/layoutMaster')
<?php
$title = isset($data) ? 'Edit' : 'Add' . " OLT";
?>
@if(isset($data)) @php $form_action = route("olt.update", $data->id); @endphp @else @php $form_action = route("olt.store"); @endphp @endif
@section('title') {{$title}} @endsection

@section('content')
<h4 class="fw-bold"><span class="text-muted fw-light">Network/</span> {{$title}}</h4>
<!-- Basic Layout -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{$form_action}}" method="POST">
            @if(isset($data))
            @method('put')
            @endif
            @csrf
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="name">OLT Name <b class="font_16 text-danger">*</b></label>
                    @if($errors->has('name'))<span class="text-danger"> {{$errors->first('name')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->name : old('name') }}" class="form-control" id="name" name="name" placeholder="OLT Name" />
                </div>

                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="mac">MAC <b class="font_16 text-danger">*</b></label>
                    @if($errors->has('mac'))<span class="text-danger"> {{$errors->first('mac')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->mac : old('mac') }}" class="form-control" id="mac" name="mac" placeholder="Mac" />
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="type">Type <b class="font_16 text-danger">*</b></label>
                    @if($errors->has('type'))<span class="text-danger"> {{$errors->first('type')}}</span> @endif
                    <select id="type" name="type" class="select2 form-select">
                        <option value="">Please Select One</option>
                        <option {{isset($data) && $data->type == 'EPON' ?'selected' :'' }} value="EPON">EPON</option>
                        <option {{isset($data) && $data->type == 'GPON' ?'selected' :'' }} value="GPON">GPON</option>
                        <option {{isset($data) && $data->type == 'XGPON' ?'selected' :'' }} value="XGPON">XGPON</option>
                    </select>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label text-uppercase" for="pon">olt ip <b class="font_16 text-danger">*</b></label>
                    @if($errors->has('olt_ip'))<span class="text-danger"> {{$errors->first('olt_ip')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->olt_ip : old('olt_ip') }}" class="form-control" id="olt_ip" name="olt_ip" placeholder="olt_ip" />
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="zone_id">Zone <b class="font_16 text-danger">*</b></label>
                    @if($errors->has('zone_id'))<span class="text-danger"> {{$errors->first('zone_id')}}</span> @endif
                    <select id="zone_id" name="zone_id" class="select2 form-select">
                        <option value="">Please Select One</option>
                        @foreach($zones as $zone)
                        <option {{isset($data) && $data->zone_id == $zone->id ?'selected' :'' }} value="{{$zone->id}}">{{$zone->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="sub_zone_id">Sub-Zone</label>
                    @if($errors->has('sub_zone_id'))<span class="text-danger"> {{$errors->first('sub_zone_id')}}</span> @endif
                    <select id="sub_zone_id" name="sub_zone_id" class="select2 form-select">
                        <option value="">Please Select One</option>
                        @foreach($sub_zones as $sub_zone)
                        <option {{isset($data) && $data->sub_zone_id == $sub_zone->id ?'selected' :'' }} value="{{$sub_zone->id}}">{{$sub_zone->name}}</option>
                        @endforeach
                    </select>
                </div>
               
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="pon">No Of PON Port <b class="font_16 text-danger">*</b></label>
                    @if($errors->has('pon'))<span class="text-danger"> {{$errors->first('pon')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->non_of_pon_port : old('pon') }}" class="form-control" id="pon" name="pon" placeholder="No Of PON Port" />
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="management_ip">Management IP</label>
                    @if($errors->has('management_ip'))<span class="text-danger"> {{$errors->first('management_ip')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->management_ip : old('management_ip') }}" class="form-control" id="management_ip" name="management_ip" placeholder="Management IP" />
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="total_onu">Total ONU <b class="font_16 text-danger">*</b></label>
                    @if($errors->has('total_onu'))<span class="text-danger"> {{$errors->first('total_onu')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->total_onu : old('total_onu') }}" class="form-control" id="total_onu" name="total_onu" placeholder="Total ONU" />
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="vlan_id">Management VLAN ID</label>
                    @if($errors->has('vlan_id'))<span class="text-danger"> {{$errors->first('vlan_id')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->management_vlan_id : old('vlan_id') }}" class="form-control" id="vlan_id" name="vlan_id" placeholder="Management VLAN ID" />
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label" for="vlan_ip">Management VLAN IP</label>
                    @if($errors->has('vlan_ip'))<span class="text-danger"> {{$errors->first('vlan_ip')}}</span> @endif
                    <input type="text" value="{{isset($data) ? $data->management_vlan_ip : old('vlan_ip') }}" class="form-control" id="vlan_ip" name="vlan_ip" placeholder="Management VLAN IP" />
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{route('olt.index')}}" class="btn btn-warning">Back</a>
            </div>

        </form>
    </div>
</div>
@endsection