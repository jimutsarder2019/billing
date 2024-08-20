@extends('layouts/layoutMaster')
@section('title') {{'olt'}} @endsection
@section('content')
<div class="row">
  <div class="col-sm-12 col-md-12 m-auto">
    <div class="card">
      <div class="card-header text-center pb-0">
        <h5 class="card-title mb-3 text-center">OLT MACS</h5>
        <h5 class="text-success mb-1">Name: {{$olt->name}}</h5>
        <h5 class="text-success mb-1">Mac: {{$olt->mac}}</h5>
      </div>
      <div class="card-datatable table-responsive">
        <table class="datatables-users table">
          <thead>
            <tr class="text-center">
              <th>macbAddr Index</th>
              <th>mac Vlan Id</th>
              <th>mac Addr</th>
              <th>mac Type</th>
              <th>mac Port Id</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $item)
            <tr class="text-center">
              <td>{{$item->macAddrIndex}}</td>
              <td>{{$item->macVlanId}}</td>
              <td>{{$item->macAddr}}</td>
              <td>{{$item->macType}}</td>
              <td>{{$item->macPortId}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection