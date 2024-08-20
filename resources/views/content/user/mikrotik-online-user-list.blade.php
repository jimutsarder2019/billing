
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
@if(auth()->user()->type == APP_MANAGER)
<div class="row g-4 mb-4">
  @if($total_online > 0)
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total Online Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$total_online}}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
  @if($total_offline_users > 0)
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total offline Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$total_offline_users}}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
  @if($total_users > 0)
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$total_users}}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endif

<div class="card">
  <div class="card-header">
    <h3 class="text-capitalize">MikroTik Online Users</h3>
    <form action='{{route("user-online-mikrotik_online_user")}}'>
      @if(auth()->user()->type == 'app_manager')
      <div class="row">
        <div class="col-md-4">
          <?php
          $mikrotik  = App\Models\Mikrotik::select('id', 'identity', 'host')->get();
          ?>
          <select class="select2 form-select form-control" name="mikrotik_id" onchange="this.form.submit()" id="">
            <option value="">Select</option>
            @foreach($mikrotik as $mkt)
            <option @if (request('mikrotik_id')==$mkt->id) selected @endif value="{{$mkt->id}}">{{$mkt->identity}} | {{$mkt->host}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <a href="{{route(Route::currentRouteName())}}" class="mx-1 btn btn-outline-warning">Clear</a>
        </div>
      </div>
      @endif
    </form>

    <style>
      .dataTables_length {
        display: none;
      }
    </style>
    <div class="row">
      <div class="col-12">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="online-tab" data-bs-toggle="tab" data-bs-target="#online" type="button" role="tab" aria-controls="home" aria-selected="true">Online ({{count($users)}})</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="offline-tab" data-bs-toggle="tab" data-bs-target="#offline" type="button" role="tab" aria-controls="profile" aria-selected="false">Offline ({{count($offline_user_list)}})</button>
          </li>
        </ul>
        <div class="px-0 tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="online" role="tabpanel" aria-labelledby="online-tab">
            <div class="card-datatable table-responsive">
              <table id="b5-dataTable" class="table  table">
                <thead>
                  <tr>
                    <th>SL No.</th>
                    <th>Name</th>
                    <th>service</th>
                    <th>CALLER-ID</th>
                    <th>ADDRESS</th>
                    <th>UPTIME</th>
                    <th>RADIUS</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($users as $index=>$user)
                  <tr>
                    <td>{{$index+1 }}</td>
                    <td>{{$user['name']}}</td>
                    <td>{{$user['service']}}</td>
                    <td>{{$user['caller-id']}}</td>
                    <td>{{$user['address']}}</td>
                    <td>{{$user['uptime']}}</td>
                    <td>{{$user['radius']}}</td>
                    <td>
                      <a class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to disconnect it')" href="{{route('mikrotik-online-disconnect', ['id'=>request()->query('mikrotik_id'),'name' => $user['name']])}}" title="Disconnect User"><i class="bi bi-x-lg"></i></a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="offline" role="tabpanel" aria-labelledby="offline-tab">
            <div class="card-datatable table-responsive">
              <table id="b5-dataTable_offline" class="table  table">
                <thead>
                  <tr>
                    <th>SL No.</th>
                    <th>Name</th>
                    <th>profile</th>
                    <th>last-logged-out</th>
                    <th>last-caller-id</th>
                    <th>last-disconnect-reason</th>
                    <th>billing status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($offline_user_list as $index=>$user)
                  <tr>
                    <td>{{$index+1 }}</td>
                    <td>{{$user['name']}}</td>
                    <td>{{$user['profile']}}</td>
                    <td>{{$user['last-logged-out']}}</td>
                    <td>{{isset($user['last-caller-id']) ? $user['last-caller-id'] : 'N/A'}}</td>
                    <td>{{isset($user['last-disconnect-reason']) ? $user['last-disconnect-reason'] : 'N/A'}}</td>
                    <td>
                      <span class="text-capitalize badge bg-label-{{$user['billing_status'] == CUSTOMER_EXPIRE | $user['billing_status'] == 'pending'?'warning':'success' }}">{{$user['billing_status']}}</span>
                      <!-- {{$user['billing_status']}} -->
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection