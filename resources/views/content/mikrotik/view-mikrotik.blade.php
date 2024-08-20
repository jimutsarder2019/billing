@extends('layouts/layoutMaster')
@section('title') {{'MikroTik'}} @endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-3">Mikrotik</h5>
    <div class="row">
      <div class="col-sm-12 col-md-10">
        <form action='{{route(Route::currentRouteName())}}'>
          <div class="d-flex">
            <select class="form-control w-10" name="item" onchange="this.form.submit()" id="">
              <option @if ($mikrotiks->count() == '10') selected @endif value="10">10</option>
              <option @if ($mikrotiks->count() == '50') selected @endif value="50">50</option>
              <option @if ($mikrotiks->count() == '100' ) selected @endif value="100">100</option>
              <option @if ($mikrotiks->count() == $mikrotiks->total()) selected @endif value="{{$mikrotiks->total()}}">All</option>
            </select>
            <div class="input-group">
              <input type="search" name="search_query" class="form-control w-75" value="{{request()->search_query}}" placeholder="Search">
              <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
            <a href="{{route(Route::currentRouteName())}}" class="mx-1 btn btn-outline-warning">Clear</a>
          </div>
        </form>
      </div>
      @can('Mikrotik Add')
      <div class="col-sm-12 col-md-2">
        <div class="">
          <a href="{{route('mikrotik-add-mikrotik')}}" class="btn btn-sm btn-primary ml-4">New Mikrotik</a>
        </div>
      </div>
      @endcan
    </div>
  </div>
  <table class="datatables-users table border-top">
    <thead>
      <tr>
        <th>SL No.</th>
        <th><i class="fa fa-cogs"></i></th>
        <th>Mikrotik Identity</th>  
        <th>Mikrotik IP</th>
        <th>API User Name</th>
        <th>API Port</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($mikrotiks as $mikrotik)
      <tr>
        <td>{{$mikrotik->id}}</td>
        <td>
          <div class="dropdown">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
            <div class="dropdown-menu">
              @can('Mikrotik Edit')
              <a class="dropdown-item cursor-pointer text-warning" href="{{route('mikrotik-edit-mikrotik', $mikrotik->id)}}">
                <i class="bi bi-pencil-square"></i> Edit
              </a>
              @endcan
              @can('Mikrotik Sync')
              <div title="Sync Mikrotik" data-bs-toggle="modal" data-bs-target="#addNewCCModal_{{$mikrotik->id}}">
                <div class="dropdown-item cursor-pointer text-success">
                  <i class="bi bi-arrow-repeat"></i> Sync From MikroTik
                </div>
              </div>
              @endcan
              <a title="Check Graph" class="dropdown-item cursor-pointer text-info" href="{{route('mikrotik_info', $mikrotik->id)}}">
                <i class="bi bi-info-circle"></i> Interface Traffic Status
              </a>
              <a title="system resource print" class="dropdown-item cursor-pointer text-primary" href="{{route('mikrotik_system_resource', $mikrotik->id)}}">
                <i class="bi bi-gear-wide-connected"></i> MikroTik Resource Details
              </a>
              @can('Mikrotik Delete')
              <form action='{{ route("mikrotik.destroy",$mikrotik->id)}}' method="post">
                @csrf
                @method('DELETE')
                <button onclick="return confirm('Are you sure to delete')" type="submit" class="dropdown-item cursor-pointer text-danger">
                  <i class="bi bi-trash"></i> Delete
                </button>
              </form>
              @endcan
            </div>
        </td>
        <td>{{$mikrotik->identity}}</td>
        <td>{{$mikrotik->host}}</td>
        <td>{{$mikrotik->username}}</td>
        <td>{{$mikrotik->port}}</td>
        <td><span class="badge bg-label-{{$mikrotik->status == 1 ? 'success': 'warning'}}">{{$mikrotik->status == 1 ? 'ON': 'OFF'}}</span></td>

      </tr>
      @include('_partials/_modals/modal-add-new-cc', ['id' => $mikrotik->id])
      @endforeach
    </tbody>
  </table>
  <div class="ml-4 data_table_pagination">{{ $mikrotiks->appends(['search_query'=>request('search_query')])->links() }}</div>
</div>
@endsection