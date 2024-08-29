@extends('layouts/layoutMaster')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/forms-pickers.js')}}"></script>
@endsection

@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title') {{$route}} @endsection
@section('content')

<div class="row g-4 mb-4">
  @if(auth()->user()->type == FRANCHISE_MANAGER || auth()->user()->hasRole(SUPER_ADMIN_ROLE))
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
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Active Customers </span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$active_customer}}</h4>
              <!-- <span class="text-success">(+18%)</span> -->
            </div>
          </div>
          <span class="badge bg-label-danger rounded p-2">
            <i class="ti ti-user-plus ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  @endif

  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Pending Customers</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$pending_customer}}</h4>
            </div>
          </div>
          <span class="badge bg-label-success rounded p-2">
            <i class="ti ti-user-check ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Expire Customers</span>
            <div class="d-flex align-items-center my-1">
              <?php
              $expire = App\Models\Customer::select('status', 'manager_id')
                ->when(auth()->user(), function ($q) {
                  if (auth()->user()->type == FRANCHISE_MANAGER) {
                    $q->where('manager_id', auth()->user()->id);
                  }
                })
                ->where('status', 'expire')->count();
              $mkt_disabled = App\Models\Customer::select('mikrotik_disabled', 'manager_id')
                ->when(auth()->user(), function ($q) {
                  if (auth()->user()->type == FRANCHISE_MANAGER) {
                    $q->where('manager_id', auth()->user()->id);
                  }
                })
                ->where('mikrotik_disabled', STATUS_TRUE)->count();
              $ttl_count = $expire - $mkt_disabled
              ?>
              <h4 class="mb-0 me-2">{{$ttl_count > 0 ? $ttl_count :0}}</h4>
            </div>
          </div>
          <span class="badge bg-label-success rounded p-2">
            <i class="ti ti-user-check ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="card">
  <div class="card-header p-1">
    <form class="mb-1" action='{{route("user-view-user")}}'>
      <div class="row">
        <div class="col-md-3">
          <h3 class=""> <span class="">View Users</span></h3>
        </div>
        <div class="col-md-3 px-0">
          <select class="form-control mr-4" name="select_mikrotik" onchange="this.form.submit()" id="">
            <option>Select Mikrotik</option>
            @foreach($mikrotiks as $mkt_item)
            <option @if (request('select_mikrotik')==$mkt_item->id) selected @endif value="{{$mkt_item->id}}">{{$mkt_item->identity}}</option>
            @endforeach
          </select>
        </div>
        @if(auth()->user()->type !== FRANCHISE_MANAGER)
        <div class="col-md-3 px-0">
          <?php
          $managers = App\Models\Manager::select('id', 'name', 'type')->with('customers')->get();
          ?>
          <div class="">
            <select class="select2 form-select" name="manager" onchange="this.form.submit()" id="">
              <option>Select Manager</option>
              @foreach($managers as $m_item)
              <option @if (request('manager')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{$m_item->type}} | ({{$m_item->customers->count()}})</option>
              @endforeach
            </select>
          </div>
        </div>
        @endif
      </form>
      
        <div class="col-2"> @can('Invoice Add')
          <div class=""> <button  type="button" data-bs-toggle="modal" data-bs-target="#customer_import" class="btn btn-sm btn-primary">Import User</button></div>
          @include('content/customer/customer-import-modal')
          @endcan
        </div>
      </div>
    <!-- include('content/customer/customer-bulk-expire-return-modal') -->
    @if(session()->has('updated_customer'))
    @include('content/customer/customer-bulk-expire-return-modal',['updated_customer'=>session()->get('updated_customer'), 'non_updated_customer'=>session()->get('non_updated_customer')])
    <!-- {{session()->get('non_updated_customer')}} -->
    @endif
    <div class="row">
      <div class="col-sm-12 col-md-12 pr-0">
        <form action='{{route("user-view-user")}}'>
          <input type="hidden" name="select_mikrotik" value="{{request('select_mikrotik')}}">
          <div class="row">
            <div class="col-md-4 d-flex pr-0">
              <div class="row">
                <div class="col-5">
                  <button disabled name="data" id="get_bulk_grace_items" class="btn btn-sm btn-sm btn-primary">Bulk Renew</button>
                </div>
                <div class="col-6 d-flex">
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
              </div>
            </div>
            <div class="col-md-7 p-0">
              <div class="row">
                <div class="col-md-10 pr-0">
                  <div class="input-group d-flex">
                    <input type="text" name="date_range" value="{{request()->date_range}}" class="form-control" style="min-width: 218px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
                    <input type="search" name="search_query" class="form-control" value="{{request()->search_query}}" placeholder="Search" id="">
                    <button type="submit" class="btn btn-outline-primary">Search</button>
                  </div>
                </div>
                <div class="col-md-2 p-0">
                  <a href="{{route('user-view-user')}}" class="btn btn-outline-warning">Clear</a>
                </div>
              </div>
            </div>
          </div>
        </form>
        @include('content/balk-renew/index')
      </div>
    </div>
  </div>
  <div class="card-datatable table-responsive pb-3">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>
            <input type="checkbox" class="form-check-input" id="check-all">
            SL No.
          </th>
          <th><i class="fa fa-cogs"></i></th>
          <th>Name</th>
          <th>Phone</th>
          <th>Username</th>
          <th>Mikrotik</th>
          <th>Package</th>
          <th>Bill</th>
          <th>Discount</th>
          <th>Expire Date</th>
          <th>Wallet</th>
          <th>Zone</th>
          <th>Manager</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <style>
          /* Hide the accordion icon */
          .accordion-button::after {
            display: none;
          }
        </style>
        @foreach($users as $index=>$user)
        <tr class="{{$user->status == CUSTOMER_PENDING ?'text-warning':''}}">
          <td>
            <input type="checkbox" name="selected_for_grace_customers[]" value="{{$user}}" class="form-check-input check-item">
            {{$user->id}}
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @if($user->status == 'pending')
                @can('Confirm Payment')
                <a href="{{route('confirm_payment', $user->id)}}" class="dropdown-item cursor-pointer text-primary"><i class="bi bi-check-lg me-1"></i>Approve Customer</a>
                @endcan
                @endif
                @can('View User')
                <a href="{{route('customer-user.show', $user->id)}}" class="dropdown-item cursor-pointer text-success"><i class="bi bi-eye-fill me-1"></i> View Profile</a>
                @endcan
                @if($user->status !== CUSTOMER_PENDING)
                @can('User Change Package')
                <a class="dropdown-item cursor-pointer text-info" href="{{route('customer_change_package_get', $user->id)}}"> <i class="bi bi-pencil-square me-1"></i> Change Package</a>
                @endcan
                @endif
                @can('User Edit')
                <a class="dropdown-item cursor-pointer text-warning" href="{{route('user-edit-customer', $user->id)}}"> <i class="bi bi-pencil-square me-1"></i> Edit User</a>
                @endcan
                @if($user->status !== CUSTOMER_PENDING)
                @can('User Change expire date')
                <a href="{{route('user-change-expire-date', $user->id)}}" class="dropdown-item text-success"><i class="bi bi-calendar me-1"></i> Change Expire Date</a>
                @endcan
                @can('User Allow Grace')
                @if(!isset($user->allow_grace) && $user->status == CUSTOMER_EXPIRE && auth()->user()->type == 'app_manager')
                <a href="{{route('customer_grace_page', $user->id)}}" class="cursor-pointer dropdown-item text-warning"><i class="bi bi-cash-coin me-1"></i> Add Grace</a>
                <!-- elseif(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
                  <a href="{{route('customer_grace_page', $user->id)}}" class="cursor-pointer dropdown-item text-warning"><i class="bi bi-cash-coin me-1"></i> Add Grace</a> -->
                @endif
                @endcan
                @can('User Create Invoice')
                <a href="{{route('add_invoice', $user->id)}}" class="cursor-pointer dropdown-item text-primary"><i class="bi bi-cash-coin"></i> Add Invoice</a>
                @endcan
                @endif
                <!-- @can('User Delete')
                <a onclick="return confirm('Are you sure to delete')" href="{{route('customer_delete', $user->id)}}" class="dropdown-item cursor-pointer text-danger"><i class="bi bi-trash"></i> Delete</a>
                @endcan -->
              </div>
          </td>
          <td>{{$user->full_name}}</td>
          <td>

          <div class="accordion accordion-flush" id="accordionFlushExample">
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne-{{$index}}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#phone-collapseOne-{{$index}}" aria-expanded="false" aria-controls="phone-collapseOne">
                    <i class="ti ti-eye-off"></i>
                  </button>
                </h2>
                <div id="phone-collapseOne-{{$index}}" class="accordion-collapse collapse" aria-labelledby="headingOne-{{$index}}" data-bs-parent="#accordionFlushExample">
                  <small class="">{{$user->phone}}</small>
                </div>
              </div>
            </div>

          </td>
          <td><a href="{{route('customer-user.show', $user->id)}}">{{$user->username}}</a>
            <div class="accordion accordion-flush" id="accordionFlushExample">
              <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingOne-{{$index}}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne-{{$index}}" aria-expanded="false" aria-controls="flush-collapseOne">
                    <i class="ti ti-eye-off"></i>
                  </button>
                </h2>
                <div id="flush-collapseOne-{{$index}}" class="accordion-collapse collapse" aria-labelledby="flush-headingOne-{{$index}}" data-bs-parent="#accordionFlushExample">
                  <small class="">Password: {{$user->password}}</small>
                </div>
              </div>
            </div>
          </td>
          <td>{{$user->mikrotik ? $user->mikrotik->identity : 'N/A'}}
            @can('Users MikrotTik Status Change')
            <span data-bs-toggle="tooltip" data-bs-placement="{{$index % 2 == 0 ? 'right':'left' }}" title="click here to {{$user->mikrotik_disabled ==  STATUS_TRUE ? 'enable' : 'disable'}} this user in mikrotik">
              <a onclick="return confirm('Are you sure to {{$user->mikrotik_disabled == STATUS_TRUE ? STATUS_ENABLED : STATUS_DISABLE }} this user');" href="{{route('disable-customer', ['id'=>$user->id, 'status'=>$user->mikrotik_disabled == STATUS_TRUE ? STATUS_FALSE : STATUS_TRUE])}}" class="mt-2 btn btn-sm btn-label-{{$user->mikrotik_disabled ==  STATUS_TRUE ? 'warning' : 'success'}}">{{$user->mikrotik_disabled ==  STATUS_TRUE ? 'Disable' : 'Enable'}}</a>
            </span>
            @endcan
            @cannot('Users MikrotTik Status Change')
            <span class="mt-2 btn btn-sm btn-label-{{$user->mikrotik_disabled ==  STATUS_TRUE ? 'warning' : 'success'}}"> {{$user->mikrotik_disabled ==  STATUS_TRUE ? 'Enable' : 'Disable'}}</span>
            @endcannot
          </td>
          <td>{{$user->package ? $user->package->name : 'N/A'}}</td>
          <td>{{$user->bill}}</td>
          <td>{{$user->discount}}</td>
          <td><span class="text-info">{{ $user->expire_date !== null ? \Carbon\Carbon::parse($user->expire_date)->format('Y-m-d h:i:s A'): "NA"}}</span></td>
          <td>{{$user->wallet}}</td>
          <td>{{$user->zone ? $user->zone->name : 'N/A'}}</td>
          <td>{{$user->manager? $user->manager->name : 'N/A'}}</td>
          <td>
            <span class="text-capitalize badge bg-label-{{$user->status == CUSTOMER_EXPIRE | $user->status == 'pending'?'warning':'success' }}">{{$user->status}}</span>
            @if($user->allow_grace !== null)<small class="badge bg-label-danger mt-1">Grace {{$user->allow_grace}} days</small> @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $users->appends(['item'=>request('item'),'select_mikrotik'=>request('select_mikrotik'),'manager' => request('manager'),'search_query'=>request('search_query') ])->links() }}</div>
  </div>
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
        console.log(item);
        var expireDate = new Date(item.expire_date);
        var wallet = item.wallet;
        var bill = item.bill;
        expireDate.setMonth(expireDate.getMonth() + 1);
        // const auth_user_type = document.querySelector('input[name="auth_user_type"]').value;
        // console.log(auth_user_type);
        var formattedDate = expireDate.toISOString().slice(0, 10); // Formatting to YYYY-MM-DD
        var formattedTime = expireDate.toLocaleTimeString('en-US', {
          hour12: false
        }); // Formatting time
        var new_exp_date = formattedDate + ' ' + formattedTime;
        html += `<tr class="${item.bill <item.wallet?'alert-success':'' }">
                  <td class="p-1 border-end"><input type="checkbox" name="selected_for_customers[]" checked value="${item.id}|${new_exp_date}" class="form-check-input selected_check-item"></td>
                  <td class="p-1 border-end"> ${item.username}</td>
                  <td class="p-1 border-end">${item.wallet}</td>
                  <td class="p-1 border-end">${item.bill}</td>
                  <td class="p-1 border-end">${item.expire_date}</td>
                  <td class="p-1"><input class="form-control" type="datetime-local" value="${new_exp_date}"> </td>
                </tr>`
      });
      document.getElementById("checked_preview_customer_list_for_allow_grace").innerHTML = html
      $("#bulk_grace_model").modal('show');
    }
  });
</script>
<script>
  $(document).ready(function() {
    // Check/uncheck all items and groups
    $('#check-all').change(function() {
      $('.check-item').prop('checked', $(this).prop('checked'));
    });
  });
</script>
@endpush