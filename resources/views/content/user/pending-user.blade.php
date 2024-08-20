@extends('layouts/layoutMaster')

@section('title')
{{ str_replace(['/','-'],' ', Request::path())}}
@endsection
@section('content')
<div class="card">
  <form action="{{route('mkt_pendingcustomer_assign_franchise')}}" method="post">
    @csrf
    <div class="card-header">
      <div class="row">
        <div class="col-sm-12 col-md-3">
          <h5 class="card-title mb-3">Pending Users </h5>
        </div>
        <div class="col-sm-12 col-md-9">
          <div class="row">
            <div class="col-8 visually-hidden" id="frinchise_managers">
              @if($errors->has('franchise_manager'))<span class="text-danger"> {{$errors->first('franchise_manager')}}</span> @endif
              <div class="d-flex">
                <label for="">Assign Franchise</label>
                <?php
                $frinchise  = App\Models\Manager::select('id', 'name', 'type')->where('type', 'franchise')->get();
                ?>
                <select class="pl-2 form-control" name="franchise_manager">
                  <option value="" selected>Select One</option>
                  @foreach($frinchise as $f_item)
                  <option value="{{$f_item->id}}">{{$f_item->name}}</option>
                  @endforeach
                </select>
                <input type="submit" value="Save" class="btn btn-xs btn-primary">
              </div>
            </div>
          </div>
        </div>
      </div>
      @if($errors->has('selected_customers'))<span class="text-danger"> {{$errors->first('selected_customers')}}</span> @endif
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-users table">
        <thead>
          <tr>
            <th>Actions</th>
            <td>
              SL No.
              <input type="checkbox" class="form-check-input d-none" id="check-all">
            </td>
            <th>Username</th>
            <th>Password</th>
            <th>Mikrotik</th>
            <th id="added_to_customer" class="d-none">Added As Customer</th>
            <th>Name</th>
            <th>Email</th>
            <th>National ID</th>
            <th>Phone</th>
            <th>Zone</th>
            <th>Registration Date</th>
            <th>Connection Date</th>
            <th>Package</th>
            <th>Bill</th>
            <th>Discount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="manual_added_users">
          @foreach($users as $user)
          <tr>
          <td class="">
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                <div class="dropdown-menu">
                  @can('View User')
                  <a href="{{route('customer-user.show', $user->id)}}" class="dropdown-item text-info">
                    <i class="bi bi-eye"></i> View
                  </a>
                  @endcan
                  @can('User Edit')
                  <a href="{{route('user-edit-customer', $user->id)}}" class="text-warning dropdown-item">
                    <i class="bi bi-pencil-square"></i> Edit
                  </a>
                  @endcan
                  @can('Confirm Payment')
                  <a href="{{route('confirm_payment', $user->id)}}" class="dropdown-item mx-1 text-success">
                    <i class="bi bi-check-lg"></i> Approve Customer
                  </a>
                  @endcan
                  @can('User Delete')
                  <a onclick="return confirm('Are You Sure to delete ? It Will Delete From Database Permanently')" href="{{route('customer_delete_permanently', $user->id)}}" class="dropdown-item mx-1 text-danger">
                    <i class="bi bi-trash"></i> Delete Permanantly
                  </a>
                  @endcan
            </td>
            <td>{{$user->id}}</td>
            <td>{{$user->username}}</td>
            <td>{{$user->password}}</td>
            <td>{{$user->mikrotik ? $user->mikrotik->identity :''}}</td>
            <td>{{$user->full_name}}</td>
            <td>{{$user->email}}</td>
            <td>{{$user->national_id}}</td>
            <td>{{$user->phone}}</td>
            <td>{{$user->zone ? $user->zone->name : '' }}</td>
            <td>{{$user->registration_date}}</td>
            <td>{{date('m/d/Y', strtotime($user->connection_date))}}</td>
            <td>{{$user->package ? $user->package->name : ''}}</td>
            <td>{{$user->bill}}</td>
            <td>{{$user->discount}}</td>
            <td>{{$user->status}}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </form>
</div>
@endsection
<script>
  function toggleManualAndMikrotikUsers() {
    let user_view = document.getElementById('user_view').value;
    if (user_view == 'manual') {
      document.getElementById('manual_added_users').classList.remove('d-none');
      document.getElementById('mikrotik_added_users').classList.add('d-none');
      document.getElementById('added_to_customer').classList.add('d-none');
    } else {
      document.getElementById('manual_added_users').classList.add('d-none');
      document.getElementById('mikrotik_added_users').classList.remove('d-none');
      document.getElementById('frinchise_managers').classList.remove('visually-hidden');
      document.getElementById('check-all').classList.remove('d-none');
      document.getElementById('added_to_customer').classList.remove('d-none');
    }
  }
</script>
@push('pricing-script')
<script>
  $(document).ready(function() {
    // Check/uncheck all items and groups
    $('#check-all').change(function() {
      $('.check-item').prop('checked', $(this).prop('checked'));
    });
  });
</script>
@endpush