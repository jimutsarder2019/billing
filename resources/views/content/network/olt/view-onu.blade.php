@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title') {{$route}} @endsection

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

@endsection

@section('page-script')
<script>
  new DataTable('#b5-dataTable', {
    pagingType: 'full_numbers'
  });
  new DataTable('#b5-dataTable_offline', {
    pagingType: 'full_numbers'
  });
</script>
@endsection

<style>
  #b5-dataTable_wrapper .dataTables_info,
  #b5-dataTable_wrapper .dataTables_paginate,
  #b5-dataTable_wrapper .dataTables_length {
    display: block !important;
  }

  #b5-dataTable_offline_length,
  .dataTables_info,
  .dataTables_paginate,
  #b5-dataTable_offline_paginate,
  #b5-dataTable_offline_info #b5-dataTable_offline .dataTables_info,
  #b5-dataTable_offline .dataTables_paginate,
  #b5-dataTable_offline .dataTables_length {
    display: block !important;
  }
</style>

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="text-capitalize">View Olt Onu</h3>
    <form action='{{route(Route::currentRouteName())}}'>
      @if(auth()->user()->type == 'app_manager')
      <div class="row">
        <div class="col-md-2">
          <?php
          $manager  = App\Models\Manager::select('id', 'name', 'type')->get();
          ?>
          <label for="">Manager</label>
          <select class="select2 form-select form-control" name="manager_id" onchange="this.form.submit()" id="">
            <option value="">Select</option>
            @foreach($manager as $m_item)
            <option @if (request('manager_id')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{$m_item->type}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <?php
          $olt  = App\Models\OLT::select('id', 'name', 'type')->get();
          ?>
          <label for="">OLT</label>
          <select class="select2 form-select form-control" name="olt_id" id="olt_list" onchange="get_olts_pon()">
            <option value="">Select</option>
            @foreach($olt as $olt_item)
            <option @if (request('olt_id')==$olt_item->id) selected @endif value="{{$olt_item->id}}">{{$olt_item->name}} | {{$olt_item->type}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label for="">PON</label>
          <select class="select2 form-select form-control" name="pon_id" id="pon_id">
            @if(request('olt_id'))
            <option>Select</option>
            <?php
            $pon = App\Models\OLTPonPortStatus::select('transceiver_pon_index', 'o_l_t_id')->where('o_l_t_id', request('olt_id'))->orderBy('transceiver_pon_index', 'asc')->get();
            ?>
            @foreach($pon as $pon_item)
            <option {{request('pon_id')==$pon_item->transceiver_pon_index ? 'selected':''}} value="{{$pon_item->transceiver_pon_index}}">Pon {{$pon_item->transceiver_pon_index}}</option>
            @endforeach
            @endif

          </select>
        </div>
        <div class="col-md-2">
          <label for="">Power</label>
          <select class="select2 form-select form-control" name="power" id="power">
            <option>Select</option>
            <option {{request('power')==1 ? 'selected':''}} value="1">Normal (-8 To -25)</option>
            <option {{request('power')==2 ? 'selected':''}} value="2">High (> -25)</option>
            <option {{request('power')==3 ? 'selected':''}} value="3">Low (< -8)</option>
          </select>
        </div>
        <div class="col-md-1 d-flex">
          <div> <button type="submit" class="mt-4 mx-1 btn btn-outline-primary">Search</a> </div>
          <div> <a href="{{route(Route::currentRouteName())}}" class="mt-4 mx-1 btn btn-outline-warning">Clear</a> </div>
        </div>
      </div>
      @endif
    </form>

    <style>
      .dataTables_length {
        display: none;
      }

      table#b5-dataTable tbody td {
        font-size: small;
        padding: 5px;
        text-align: center;
      }
    </style>
    <div class="row">
      <div class="col-12">
        <div class="card-datatable table-responsive">
          <table id="b5-dataTable" class="table  table">
            <thead>
              <tr>
                <th>SL No.</th>
                <th>Customer</th>
                <th>power</th>
                <th>pon</th>
                <th>onu</th>
                <th>onu mac</th>
                <th>Reason</th>
                <th>Time</th>
                <th>OLT</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($data as $index=>$item)
              <tr>
                <td>{{$index+1 }}</td>
                <td>{{"item"}}</td>
                <td><span class="{{$item->power !== '-' ? 'badge bg-label-success':''}} "> {{$item->power}} </span></td>
                <td>{{$item->pon}}</td>
                <td>{{$item->onu}}</td>
                <td>{{$item->onu_mac}}</td>
                <td><span class="{{$item->deregReason !== 'power_off'  ?'badge bg-label-danger' :''}}">{{$item->deregReason}}</span></td>
                <td>{{$item->lastDeregTime}}</td>
                <td>{{$item->olt? $item->olt->name:''}}</td>
                <td><span class="badge {{ $item->phaseStatus =='Working' ||$item->phaseStatus =='Online' ?'bg-label-success':'bg-label-danger'}}">{{$item->phaseStatus}}</span></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<script script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  function get_olts_pon() {
    let olt_id = document.getElementById('olt_list').value;
    let app_url = document.head.querySelector('meta[name="app_url"]').content;
    axios.get(`${app_url}/olt/check/${olt_id}?return_json=true`).then((resp) => {
      if (resp.status == 200) {
        let pon_id = document.getElementById('pon_id');
        let option = "<option>Select pon</option>";
        console.log(resp.data.data);
        const data = resp.data.data;
        for (let i = 0; data.length > i; i++) {
          option = option.concat(`<option value=${data[i].transceiver_pon_index}>Pon ${data[i].transceiver_pon_index} </option>`)
        }
        pon_id.innerHTML = option;
      }
    });

  };
</script>