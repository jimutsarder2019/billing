@extends('layouts/layoutMaster')
@php $route = 'btrc report' @endphp
@section('title', $route)
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/forms-pickers.js')}}"></script>
@endsection
@section('content')
<div class="card">
  <div class="card-header">
    <div>
      <form action='{{route("reprot.btrc")}}' class="d-flex">
        <select class="form-control mr-1" style="width: 8%;" name="item" onchange="this.form.submit()" id="">
          <option @if ($data->count() == '10') selected @endif value="10">10</option>
          <option @if ($data->count() == '50') selected @endif value="50">50</option>
          <option @if (request('item')=='all' ) selected @endif value="all">All</option>
        </select>
        <div class="form-group d-flex">
          <input type="text" name="date_range" value="{{$req_date_range}}" class="form-control w-100" style="min-width: 318px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
          <input type="submit" class="btn btn-xs btn-outline-primary mx-md-2" value="Submit">
          <a href="{{route(Route::currentRouteName())}}" class="mx-1 btn btn-outline-warning">Clear</a>

          @can('BTRC Report Export')
          <a class="btn btn-sm btn-primary ml-4" href="{{route('reprot.btrc-export',['date_range'=>$req_date_range])}}">Export xlsx</a>
          @endcan
        </div>
      </form>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-users table">
        <thead>
          <tr>
            <th>SL</th>
            <th>Service</th>
            <th>connection_type</th>
            <th>client_name</th>
            <th>bandwidth_distribution_point</th>
            <th>connectivity_type</th>
            <th>activation_date</th>
            <th>bandwidth_allocation</th>
            <th>allocated_ip</th>
            <th>division</th>
            <th>district</th>
            <th>thana</th>
            <th>address</th>
            <th>client_mobile</th>
            <th>client_email</th>
            <th>selling_price_bdt_excluding_vat</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $index=>$item)
          <tr>
            <td>{{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}</td>
            <td>{{'PPPoE'}}</td>
            <td>Wired</td> <!-- //connection_type -->
            <td>{{$item->full_name}}</td> <!-- //client_name -->
            <td>PoP</td> <!-- //bandwidth_distribution_point -->
            <td>Shared</td> <!-- //connectivity_type -->
            <td>{{$item->connection_date}}</td> <!-- //activation_date -->
            <td>{{$item->package ? $item->package->bandwidth : '5 Mb'}}</td> <!-- //bandwidth_allocation -->
            <td>{{$item->username }}</td> <!-- //allocated_ip -->
            <td>{{$item->zone && $item->zone->upazila &&  $item->zone->upazila->district && $item->zone->upazila->district->division ? $item->zone->upazila->district->division->name : ''}}</td> <!-- //division -->
            <td>{{$item->zone && $item->zone->upazila &&  $item->zone->upazila->district && $item->zone->upazila->district ? $item->zone->upazila->district->name : ''}}</td> <!-- //district -->
            <td>{{$item->zone && $item->zone->upazila? $item->zone->upazila->name : ''}}</td> <!-- //thana -->
            <td>{{$item->address}}</td> <!-- //address -->
            <td>{{$item->phone}}</td> <!-- //client_mobile -->
            <td>{{$item->email}}</td> <!-- //client_email -->
            <td>{{$item->package ? $item->package->price :'00'}}</td> <!-- //client_email -->
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="ml-4 data_table_pagination">{{ $data->appends(['date_range'=>request('date_range')])->links() }}</div>
    </div>
  </div>
  @endsection