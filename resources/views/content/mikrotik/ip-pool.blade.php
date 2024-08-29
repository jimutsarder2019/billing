@extends('layouts/layoutMaster')

@section('title','IPPool')
@section('content')
<div class="card">
  <h5 class="card-title p-3">IP Pool</h5>
  <div class="card-header d-flex">
    <form class="d-flex w-75" action='{{route("mikrotik-ip-pool")}}'>
      <select class="form-control w-10" name="item" onchange="this.form.submit()" id="">
        <option @if ($ips->count() == '10') selected @endif value="10">10</option>
        <option @if ($ips->count() == '50') selected @endif value="50">50</option>
        <option @if ($ips->count() == '100') selected @endif value="100">100</option>
        <option @if ($ips->count() == $ips->total()) selected @endif value="{{$ips->total()}}">All</option>
      </select>
      @if(auth()->user()->type == APP_MANAGER)
      <div class="w-90 mx-2">
        <select id="mikrotik" name="mikrotik" class="select2 form-select" onchange="this.form.submit()">
          <option value="">Please Select One</option>
          @foreach($mikrotiks as $mikrotik)
          <option {{request('mikrotik') == $mikrotik->id ? 'selected' : ''}} value="{{$mikrotik->id}}">{{$mikrotik->identity}}</option>
          @endforeach
        </select>
      </div>
      <a href="{{route('mikrotik-ip-pool')}}" class="btn btn-outline-warning">Clear</a>
      @endif
    </form>
    @can('Mikrotik IP Pool Add')
    <button type="button" class="ml-4 btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewIPPoolModal">Add New</button>
    @endcan
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Name</th>
          <th>Total Number of IP</th>
          <th>Mikrotik</th>
          <th>Start IP</th>
          <th>End IP</th>
          <th>Subnet</th>
          <th>Public IP</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($ips as $ip)
        <tr>
          <td>{{$ip->id}}</td>
          <td>{{$ip->name}}</td>
          <td>{{$ip->total_number_of_ip}}</td>
          <td>{{$ip->mikrotik->identity}}</td>
          <td>{{$ip->start_ip}}</td>
          <td>{{$ip->end_ip}}</td>
          <td>{{$ip->subnet}}</td>
          <td>{{$ip->public_ip}}</td>
          <td>{{$ip->status == 0 ? 'Active' :'InActive'}}</td>
          <td class="">
            @can('Mikrotik IP Pool Edit')
            <a href="{{route('mikrotik-edit-ip-pool', $ip->id)}}">
              <div class="btn btn-sm btn-primary">
                <i class="bi bi-pencil-square"></i>
              </div>
            </a>
            @endcan
            @can('Mikrotik IP Pool Delete')
            <a onclick="return confirm('Are you sure to delete')" href="{{route('delete_ip_pool', $ip->id)}}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
            @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $ips->appends(['item'=>request('item'),'mikrotik' => request('mikrotik'),'search_query'=>request('search_query') ])->links() }}</div>
  </div>
  @include('content/mikrotik/add-ip-pool-modal', ['mikrotiks' => $mikrotiks])
</div>
@endsection