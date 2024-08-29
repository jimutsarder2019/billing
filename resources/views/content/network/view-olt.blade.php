@extends('layouts/layoutMaster')
@section('title') {{'olt'}} @endsection
@section('content')

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">OLT</h5>
    <a href='{{route("olt.create")}}' class="btn btn-primary">Add item</a>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Name</th>
          <th>Zone</th>
          <th>Sub Zone</th>
          <th>Type</th>
          <th>non of pon port</th>
          <th>management ip</th>
          <th>management vlan ip</th>
          <th>management vlan id</th>
          <th>total onu</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $item)
        <tr>
          <td>{{$item->id}}</td>
          <td>{{$item->name}}</td>
          <td>{{$item->zone->name}}</td>
          <td>{{$item->sub_zone->name}}</td>
          <td>{{$item->type}}</td>
          <td>{{$item->non_of_pon_port}}</td>
          <td>{{$item->management_ip}}</td>
          <td>{{$item->management_vlan_ip}}</td>
          <td>{{$item->management_vlan_id}}</td>
          <td>{{$item->total_onu}}</td>
          <td>
            <a class="btn btn-sm btn-primary" href="{{route('olt.edit', $item->id)}}">
              <div class="cursor-pointer">
                <i class="bi bi-pencil-square"></i>
              </div>
            </a>
            @php $url = "network/olt/$item->id" @endphp
            <button type="submit" class="btn btn-sm btn-danger" onclick='openConfirmation("{{ url($url) }}")'><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection