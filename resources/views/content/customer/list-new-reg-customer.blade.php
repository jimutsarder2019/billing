@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title') {{$route}} @endsection
@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <h3>New Register User</h3>
    <a href="{{route('customer-user.create')}}" class="btn btn-sm btn-primary">New Register</a>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Name</th>
          <th>Email</th>
          <th>National ID</th>
          <th>Phone</th>
          <th>Zone</th>
          <th>Package</th>
          <th>Bill</th>
          <th>Discount</th>
          <th>Billing Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
        <tr>
          <td>{{$user->id}}</td>
          <td>{{$user->full_name}}</td>
          <td>{{$user->email}}</td>
          <td>{{$user->national_id}}</td>
          <td>{{$user->phone}}</td>
          <td>
		  <?php if(isset($user->zone->name)){ ?>
		  {{$user->zone->name}}
		  <?php } ?>
		  </td>
          <td>
		  <?php if(isset($user->package->name)){ ?>
		  {{$user->package->name}}
		  <?php } ?>
		  </td>
          <td>{{$user->bill}}</td>
          <td>{{$user->discount}}</td>
          <td>{{$user->billing_date}}</td>
          <td>{{$user->status}}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu text-start">
                @can('User Edit')
                <a class="btn btn-sm text-primary dropdown-item" href="{{route('user-edit-customer', $user->id)}}"> <i class="me-2 bi bi-pencil-square"></i> Edit Customer</a>
                @endcan
                @can('Confirm Payment')
                <div class="cursor-pointer btn btn-sm text-primary dropdown-item" data-bs-toggle="modal" data-bs-target="#addInvoiceModal_{{$user->id}}" title="Update Connection Info "><i class="bi bi-cash-coin me-2"></i>Update Connection Info </div>
                @endcan
                @can('View User')
                <a href="{{route('customer-user.show', $user->id)}}" class="btn btn-sm text-info dropdown-item"><i class="me-2 bi bi-eye-fill"></i> View Customer</a>
                @endcan
                @can('User Delete')
                <a onclick="return confirm('Are You Sure To Delete')" href="{{route('customer_delete', $user->id)}}" class="dropdown-item btn btn-sm text-danger"><i class="me-2 bi bi-trash"></i> Delete Customer</a>
                @endcan
              </div>
            </div>
          </td>
        </tr>
        @include('content/user/collect-billWithMikroTik', ['user' => $user, 'mikrotik'=>$mikrotik])
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection