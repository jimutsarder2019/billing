@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title') {{$route}} @endsection
@section('content')
<div class="card">
  <div class="card-header">
    <h3><span>Expired Customer ({{$users->total()}})</span>
      @include('content/grace/bulk-grace-modal')
    </h3>
    <form action='{{route(Route::currentRouteName())}}'>
      <div class="row">
        <div class="col-sm-12 col-md-2">
          <button disabled id="get_bulk_grace_items" class="btn btn-sm btn-sm btn-primary">Bulk Grace</button>
        </div>
        <div class="col-sm-12 col-md-1">
          <select class="form-control" name="item" onchange="this.form.submit()" id="">
            <option @if ($users->count() == '10') selected @endif value="10">10</option>
            <option @if ($users->count() == '50') selected @endif value="50">50</option>
            <option @if ($users->count() == '100' ) selected @endif value="100">100</option>
            <option @if ($users->count() == $users->total() ) selected @endif value="{{$users->total()}}">All</option>
          </select>
        </div>
        @if(auth()->user()->type !== FRANCHISE_MANAGER)
        <?php
        $managers = App\Models\Manager::select('id', 'name', 'type')->with('expire_customers')->get();
        ?>
        <div class="col-md-3">
          <select class="select2 form-select" name="manager" onchange="this.form.submit()" id="">
            <option value="">------Select-----</option>
            @foreach($managers as $m_item)
            <option @if (request('manager')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{$m_item->type}} | ({{$m_item->expire_customers->count()}})</option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="col-md-6">
          <div class="d-flex">
            <div class="input-group">
              <input type="search" name="search_query" class="form-control w-50" value="{{request()->search_query}}" placeholder="Search" id="">
              <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
            <a href="{{route(Route::currentRouteName())}}" class="btn btn-outline-warning">Clear</a>
            <div class="dropdown">
              <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">Export</button>
              <div class="dropdown-menu p-1">
                <a href="{{route('export-expire-customer',['type'=>'pdf'])}}" class="dropdown-item cursor-pointer text-primary">PDF</a>
                <a href="{{route('export-expire-customer',['type'=>'csv'])}}" class="dropdown-item cursor-pointer text-success">CSV</a>
                <a href="{{route('export-expire-customer',['type'=>'xlsx'])}}" class="dropdown-item cursor-pointer text-success">xlsx</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th><input type="checkbox" class="form-check-input" id="check-all"></th>
          <th><i class="fa fa-cogs"></i></th>
          <th>ID No.</th>
          <th>Name</th>
          <th>Phone</th>
          <th>User Name</th>
          <th>Zone</th>
          <th>Package</th>
          <th>Bill</th>
          <th>Discount</th>
          <th>Expired Date</th>
          <th>Manager</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
        <tr>
          <td>
             <input type="checkbox" name="selected_for_grace_customers[]" value="{{$user}}" class="form-check-input check-item"></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @can('User Edit')
                <a class="dropdown-item cursor-pointer text-primary" href="{{route('user-edit-customer', $user->id)}}"> <i class="bi bi-pencil-square"></i> Edit</a>
                @endcan
                @can('User Change expire date')
                <a href="{{route('user-change-expire-date', $user->id)}}" class="dropdown-item text-success"><i class="bi bi-calendar me-1"></i> Change Expire Date</a>
                @endcan
                @can('User Create Invoice')
                <a href="{{route('add_invoice', $user->id)}}" class="dropdown-item cursor-pointer text-primary"><i class="bi bi-cash-coin"></i>Add Invoice</a>
                @endcan
                @can('User Allow Grace')
                @if(!isset($user->allow_grace) && auth()->user()->type == 'app_manager')
                <a href="{{route('customer_grace_page', $user->id)}}" class="cursor-pointer dropdown-item cursor-pointer text-warning"><i class="bi bi-cash-coin"></i> Add Grace</a>
                @endif
                @endcan
                @can('View User')
                <a href="{{route('customer-user.show', $user->id)}}" class="dropdown-item text-info"><i class="bi bi-eye-fill"></i> View</a>
                @endcan
                @can('Delete Users')
                @if($user->mikrotik_disabled == STATUS_TRUE)
                <a onclick="return confirm('Are you sure to Deete This user')" href="{{route('customer-suspended', $user->id)}}" class="dropdown-item text-danger">Delete User</a>
                @endif
                @endcan
              </div>
          </td>
          <td>{{$user->id}}</td>
          <td>{{$user->full_name}}</td>
          <td>{{$user->phone}}</td>
          <td>{{$user->username}}</td>
          <td>{{ $user->zone ? $user->zone->name :'N/A'}}</td>
          <td>{{$user->package ? $user->package->name :'N/A'}}
            <small>{{$user->mikrotik ? $user->mikrotik->identity :''}} @if(auth()->user()->type !== FRANCHISE_MANAGER) | {{$user->mikrotik->host}} @endif</small>
          </td>
          <td>{{$user->bill}}</td>
          <td>{{$user->discount}}</td>
          <td>{{$user->expire_date}}</td>
          <td>{{$user->manager ? $user->manager->name : 'N/A'}}</td>
          <td>{{$user->status}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $users->appends(['manager' => request('manager'),'search_query'=>request('search_query') ])->links() }}</div>
  </div>
</div>
@endsection


@push('pricing-script')
<script>
  $(document).ready(function() {
    // Check/uncheck all items and groups
    $('#check-all').change(function() {
      $('.check-item').prop('checked', $(this).prop('checked'));
      const checkedItems = $("input[name='selected_for_grace_customers[]']:checked");
      if (checkedItems.length > 0) {
        $("#get_bulk_grace_items").removeAttr('disabled');
      } else {
        $("#get_bulk_grace_items").attr('disabled', 'disabled');
      }

    });
    // Check/uncheck the items within a group
    $('.check-item').change(function() {
      // Uncheck "All" if any item is unchecked
      $('#check-all').prop('checked', $('.check-item').not(':checked').length === 0);
      const checkedItems = $("input[name='selected_for_grace_customers[]']:checked");
      if (checkedItems.length > 0) {
        $("#get_bulk_grace_items").removeAttr('disabled');
      } else {
        $("#get_bulk_grace_items").attr('disabled', 'disabled');
      }
    });
  });

  document.getElementById("get_bulk_grace_items").addEventListener("click", function(event) {
    document.getElementById("checked_preview_customer_list_for_allow_grace").innerHTML = ''
    event.preventDefault(); // Prevents the default behavior of the button
    const checkedItems = $("input[name='selected_for_grace_customers[]']:checked")
    if (checkedItems.length > 0) {
      var html = ''
      checkedItems.each(function() {
        var item = JSON.parse($(this).val());
        html += `<tr>
                  <td><input type="checkbox" name="selected_for_grace_customers[]" checked value="${item.id}" class="form-check-input selected_check-item"></td>
                  <td>${item.username}</td>
                  <td>${item.expire_date}</td>
                </tr>`
      });
      document.getElementById("checked_preview_customer_list_for_allow_grace").innerHTML = html
      $("#bulk_grace_model").modal('show');
    }
  });
</script>
@endpush