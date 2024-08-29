@extends('layouts/layoutMaster')
@section('title') {{'olt'}} @endsection
@section('content')
<div class="row">
  <div class="col-sm-12 col-md-6 m-auto">
    <div class="card">
      <div class="card-header text-center pb-0">
        <h5 class="card-title mb-3 text-center">PON Optical Status</h5>
        <h5 class="text-success mb-1">Name: {{$olt->name}}</h5>
        <h5 class="text-success mb-1">Mac: {{$olt->mac}}</h5>
      </div>
      <div class="card-datatable table-responsive">
        <table class="datatables-users table">
          <thead>
            <tr class="text-center">
              <th>PON</th>
              <th>tempperature</th>
              <th>transmit Power</th>
              <th>Pon Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $item)
            <tr class="text-center">
              <td>{{$item->transceiver_pon_index}}</td>
              <td>{{$item->tempperature}}</td>
              <td>{{$item->transmitPower}}</td>
              <td>{{$item->pon_status}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection