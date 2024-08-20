@extends('layouts/layoutMaster')
<?php
$title = isset($data) ? 'Edit' : 'Add' . ' Onu';
?>
@section('title')
    {{ $title }}
@endsection
@if (isset($data))
    @php $form_action = route("onu.update", $data->id); @endphp
@else
    @php $form_action = route("onu.store"); @endphp
@endif
@section('content')
    <h4 class="fw-bold "><span class="text-muted fw-light">Network/</span> {{ $title }}</h4>

    <!-- Basic Layout -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ $form_action }}" method="POST">
                @if (isset($data))
                    @method('put')
                @endif
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">ONU Name <b class="font_16 text-danger">*</b></label>
                    @if ($errors->has('name'))
                        <span class="text-danger"> {{ $errors->first('name') }}</span>
                    @endif
                    <input type="text" class="form-control" id="name" name="name" placeholder="ONU Name"
                        value="{{ isset($data) ? $data->name : old('name') }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mac">MAC Address <b class="font_16 text-danger">*</b></label>
                    @if ($errors->has('mac'))
                        <span class="text-danger"> {{ $errors->first('mac') }}</span>
                    @endif
                    <input type="text" class="form-control" id="mac" name="mac"
                        placeholder="EX: 00:11:22:33:44:55" value="{{ isset($data) ? $data->mac : old('mac') }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="olt_id">OLT <b class="font_16 text-danger">*</b></label>
                    @if ($errors->has('olt_id'))
                        <span class="text-danger"> {{ $errors->first('olt_id') }}</span>
                    @endif
                    <select id="olt_id" name="olt_id" class="select2 form-select" placeholder="OLT"
                        onchange="togglePortAndZoneField()">
                        <option value="">Please Select One</option>
                        @foreach ($olts as $olt)
                            <option
                                {{ (isset($data) && $data->olt_id == $olt->id ? 'selected' : old('olt') == $olt->id) ? 'selected' : '' }}
                                value="{{ $olt->id }}">{{ $olt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="pon_port">Pon Port <b class="font_16 text-danger">*</b></label>
                    @if ($errors->has('pon_port'))
                        <span class="text-danger"> {{ $errors->first('pon_port') }}</span>
                    @endif
                    <select id="pon_port" name="pon_port" class="select2 form-select" placeholder="PON port">
                        <option value="">Please Select One</option>
                        @isset($data)
                            @for ($i = 1; $i <= $data->olt->non_of_pon_port; $i++)
                                <option {{ $data->pon_port == $i ? 'selected' : '' }} value="{{ $i }}">
                                    {{ $i }}</option>
                            @endfor
                        @endisset
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="onu_id">Onu Id <b class="font_16 text-danger">*</b></label>
                    @if ($errors->has('onu_id'))
                        <span class="text-danger"> {{ $errors->first('onu_id') }}</span>
                    @endif
                    <input type="text" class="form-control" id="onu_id" name="onu_id" placeholder="Onu Id"
                        value="{{ isset($data) ? $data->onu_id : old('onu_id') }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="rx_power">Rx Power <b class="font_16 text-danger">*</b></label>
                    @if ($errors->has('rx_power'))
                        <span class="text-danger"> {{ $errors->first('rx_power') }}</span>
                    @endif
                    <input type="text" class="form-control" id="rx_power" name="rx_power" placeholder="Rx Power"
                        value="{{ isset($data) ? $data->rx_power : old('rx_power') }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="distance">Distance (M) <b class="font_16 text-danger">*</b></label>
                    @if ($errors->has('distance'))
                        <span class="text-danger"> {{ $errors->first('distance') }}</span>
                    @endif
                    <input type="text" class="form-control" id="distance" name="distance" placeholder="Rx Power"
                        value="{{ isset($data) ? $data->distance : old('distance') }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="user_id">Customer</label>
                    @if ($errors->has('user_id'))
                        <span class="text-danger"> {{ $errors->first('user_id') }}</span>
                    @endif
                    <select id="user_id" name="user_id" class="select2 form-select">
                        <option value="">Please Select One</option>
                        @foreach ($users as $user)
                            <option
                                {{ isset($data) && $data->customer_id == $user->id ? 'selected' : (old('user_id') == $user->id ? 'selected' : '') }}
                                value="{{ $user->id }}">{{ $user->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" id="zone_id_field" style="display:none">
                    <label class="form-label" for="zone_id">Zone</label>
                    @if ($errors->has('zone_id'))
                        <span class="text-danger"> {{ $errors->first('zone_id') }}</span>
                    @endif
                    <input type="text" class="form-control" id="zone_name" readonly name="zone_name"
                        value="{{ isset($data) ? $data->zone_name : old('zone_name') }}" />

                    <input type="hidden" class="form-control" id="zone_id" readonly name="zone_id"
                        value="{{ isset($data) ? $data->zone_id : old('zone_id') }}" />
                </div>
                <div class="mb-3 row">
                    <div class="col-3">
                        <input class="form-check-input"
                            {{ isset($data) && $data->vlan_tagged == '1' ? 'checked' : (old('vlan_tagged') == 'on' ? 'checked' : '') }}
                            type="checkbox" name="vlan_tagged" id="vlan_tagged" onchange="toggleVlanIdField()">
                        <label for="vlan_tagged">VLAN Tagged</label>
                    </div>
                    <div class="col-9" id="vlan_id_field"
                        @if (isset($data) && $data->vlan_tagged == '1') style="display:block" @elseif(old('vlan_tagged')) style="display:block" @else  style="display:none" @endif>
                        <label for="vlan_id" class="col-2 col-form-label">VLAN Id</label>
                        @if ($errors->has('vlan_id'))
                            <span class="text-danger"> {{ $errors->first('vlan_id') }}</span>
                        @endif
                        <div class="col-10">
                            <input class="form-control" type="text" id="vlan_id" name="vlan_id"
                                value="{{ isset($data) && $data->vlan_id ? $data->vlan_id : old('vlan_id') }}"
                                placeholder="VLAN ID" />
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection
@push('pricing-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"
        integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function togglePortAndZoneField() {
            let olt_id = document.getElementById('olt_id').value;
            let app_url = document.head.querySelector('meta[name="app_url"]').content;
            axios.get(`${app_url}/fttx/olt-details/${olt_id}`).then((resp) => {
                if (resp.status == 200) {
                    console.log(resp.data.no_of_pon_port);
                    let pon_port = resp.data.no_of_pon_port;
                    let pon_port_select_field = document.getElementById('pon_port');
                    let option = "";
                    for (let i = 1; i <= pon_port; i++) {
                        option = option.concat(`<option value=${i}> ${i} </option>`)
                    }
                    pon_port_select_field.innerHTML = option;
                    let zone_field = document.getElementById('zone_name');
                    let zone_field_id = document.getElementById('zone_id');
                    zone_field.value = resp.data.zone.name
                    zone_field_id.value = resp.data.zone.id
                    document.getElementById('zone_id_field').style.display = 'block';
                }
            })
        }
        // toggleVlanIdField
        function toggleVlanIdField() {
            let vlan_tagged = document.getElementById('vlan_tagged');
            if (vlan_tagged.checked == true) {
                let vlan_id_field = document.getElementById('vlan_id_field');
                vlan_id_field.style.display = 'block'
            } else {
                let vlan_id_field = document.getElementById('vlan_id_field');
                vlan_id_field.style.display = 'none'
            }
        }
    </script>
@endpush
