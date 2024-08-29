@extends('layouts/layoutMaster')
@section('title','Delete Users')
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
    <h3>Delete Users</h3>
    <form action='{{route("delete-customers")}}'>
      <div class="row">
        <div class="col-sm-12 col-md-4 d-flex">
          <select class="form-control w-25 mr-4" name="item" onchange="this.form.submit()" id="">
            <option @if ($users->count() == '10') selected @endif value="10">10</option>
            <option @if ($users->count() == '50') selected @endif value="50">50</option>
            <option @if ($users->count() == '100' ) selected @endif value="100">100</option>
            <option @if ($users->count() == $users->total() ) selected @endif value="{{$users->total()}}">All</option>
          </select>
          <input type="search" name="search_query" class="form-control w-75" value="{{request()->search_query}}" placeholder="Search" id="">
        </div>
      </div>
    </form>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Username</th>
          <th>Mikrotik</th>
          <th>Zone</th>
          <th>Package</th>
          <th>Bill</th>
          <th>Discount</th>
          <th>Billing Date</th>
          <th>Manager</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
        <tr>
          <td>{{$user->id}}</td>
          <td>{{$user->full_name}}</td>
          <td>{{$user->phone}}</td>
          <td>{{$user->username}}</td>
          <td>{{$user->mikrotik->identity}}</td>
          <td>{{$user->zone ? $user->zone->name : 'N/A'}}</td>
          <td>{{$user->package->name}}</td>
          <td>{{$user->bill}}</td>
          <td>{{$user->discount}}</td>
          <td>{{ \Carbon\Carbon::parse($user->expire_date)->format('Y-m-d h:i:s A')}}</td>
          <td>{{$user->manager->name}}</td>
          <td><span class="text-capitalize badge bg-label-danger }}">{{$user->status}}</span>
            @if($user->allow_grace !== null)<small class="badge bg-label-danger mt-1">Grace {{$user->allow_grace}} days</small> @endif
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @can('View User')
                <a href="{{route('customer-user.show', $user->id)}}" class="dropdown-item cursor-pointer text-info"><i class="bi bi-eye-fill"></i> View</a>
                @endcan

              </div>
              <!-- include('content/user/collect-bill', ['user' => $user]) -->
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $users->links() }}</div>
  </div>
</div>
@endsection