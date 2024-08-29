@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title') {{str_replace('_',' ',$route)}} @endsection
@section('content')
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total Customers</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$users->total()}}</h4>
              <!-- <span class="text-success">(+29%)</span> -->
            </div>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="ti ti-users"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
 
</div>
<div class="card">
  <div class="card-header border-bottom">
    <h3 class="text-capitalize">Grace Users</h3>
    <div class="row">
      <div class="col-sm-12 col-md-10">
        <form action='{{route("grace_user_list")}}'>
          <div class="row">
            <div class="col-md-2 d-flex">
              <select class="form-control mr-4" name="item" onchange="this.form.submit()" id="">
                <option @if ($users->count() == '10') selected @endif value="10">10</option>
                <option @if ($users->count() == '50') selected @endif value="50">50</option>
                <option @if ($users->count() == '100' ) selected @endif value="100">100</option>
                <option @if ($users->count() == $users->total() ) selected @endif value="{{$users->total()}}">All</option>
              </select>
              <select class="form-control mr-4" name="orderBy" onchange="this.form.submit()" id="">
                <option {{request('orderBy') == 'asc' ? 'selected' :'' }} value="asc">asc</option>
                <option {{request('orderBy') == 'desc' ? 'selected' :'' }} value="desc">desc</option>
              </select>
            </div>
            <div class="col-md-6">
              <div class="row">
                <div class="col-md-10">
                  <div class="input-group">
                    <input type="search" name="search_query" class="form-control" value="{{request()->search_query}}" placeholder="Search" id="">
                    <button type="submit" class="btn btn-outline-primary">Search</button>
                  </div>
                </div>
                <div class="col-md-2">
                  <a href="{{route('grace_user_list')}}" class="btn btn-outline-warning">Clear</a>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="card-datatable table-responsive pb-3">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>User Name</th>
          <th>Phone</th>
          <th>Package</th>
          <th>Bill</th>
          <th>Actual Expire Date</th>
          <th>Allow Grace</th>
          <th>Expire After Grace</th>
          <th>Manager</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
        <tr>
          <td>{{$user->id}}</td>
          <td>{{$user->username}}</td>
          <td>{{$user->phone}}</td>
          <td>{{$user->package ? $user->package->name : 'N/A'}} @if($user->mikrotik) <span class="text-success">({{ $user->mikrotik->identity}}) @endif</span></td>
          <td>{{$user->bill}}</td>
          <td>{{ $user->customerGrace->count() >0 ? $user->customerGrace->last()->grace_before_expire_date :''}}</td>
          <td><small class="badge bg-label-danger mt-1">Grace {{$user->allow_grace}} days</small></td>
          <td>{{ $user->expire_date !== null ? \Carbon\Carbon::parse($user->expire_date)->format('Y-m-d h:i:s A'): "NA"}}</td>
          <td>{{$user->manager? $user->manager->name : 'N/A'}}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @can('View User')
                <a href="{{route('customer-user.show', $user->id)}}" class="dropdown-item cursor-pointer text-info"><i class="bi bi-eye-fill"></i> View</a>
                @endcan
                @can('User Change Package')
                <a class="dropdown-item cursor-pointer text-primary" href="{{route('customer_change_package_get', $user->id)}}"> <i class="bi bi-pencil-square"></i> Change Package</a>
                @endcan
                @can('User Edit')
                <a class="dropdown-item cursor-pointer text-primary" href="{{route('user-edit-customer', $user->id)}}"> <i class="bi bi-pencil-square"></i> Edit</a>
                @endcan
                @can('User Allow Grace')
                @if(!isset($user->allow_grace) && $user->status == CUSTOMER_EXPIRE && auth()->user()->type == 'app_manager')
                <a href="{{route('customer_grace_page', $user->id)}}" class="cursor-pointer dropdown-item cursor-pointer text-warning"><i class="bi bi-cash-coin"></i> Add Grace</a>
                @elseif(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
                <a href="{{route('customer_grace_page', $user->id)}}" class="cursor-pointer dropdown-item cursor-pointer text-warning"><i class="bi bi-cash-coin"></i> Add Grace</a>
                @endif
                @endcan
                @can('User Create Invoice')
                <a href="{{route('add_invoice', $user->id)}}" class="cursor-pointer dropdown-item cursor-pointer text-primary"><i class="bi bi-cash-coin"></i> Add Invoice</a>
                @endcan
              </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $users->appends(['manager' => request('manager'),'search_query'=>request('search_query') ])->links() }}</div>
  </div>
</div>
@endsection