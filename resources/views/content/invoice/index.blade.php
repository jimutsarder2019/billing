@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title',$route)


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
@section('content')
<div class="card">
  <div class="card-header">
    <form action='{{route(Route::currentRouteName())}}'>
      <div class="row">
        <div class="col-6">
          <h4 class="text-capitalize">
            {{ str_replace('-',' ', $route) }} ({{$data->total()}})
          </h4>
        </div>
        <div class="col-6">
          <div class="d-flex">
            <input type="text" name="date_range" value="{{request('date_range')}}" class="form-control w-100" style="min-width: 118px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
            <div class="dropdown">
              <button type="button" class="btn btn-primary btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                Export
              </button>
              <div class="dropdown-menu">
                <a href="{{route(Route::currentRouteName(),['date_range'=>request('date_range'), 'item'=>request('item'), 'search_query'=>request('search_query'),'manager'=>request('manager'), 'status'=>request('status'), 'invoice_for'=>request('invoice_for'), 'export'=>'pdf' ] ) }}" class="dropdown-item text-warning" role="button" aria-label="Export PDF" title="Export PDF">Export PDF</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 col-md-11">
          <div class="row">
            <div class="col-md-5 p-0 d-flex">
              <select class="form-control" name="item" onchange="this.form.submit()" id="">
                <option @if ($data->count() == '10') selected @endif value="10">10</option>
                <option @if ($data->count() == '50') selected @endif value="50">50</option>
                <option @if ($data->count() == '100' ) selected @endif value="100">100</option>
                <option @if ($data->count() == $data->total()) selected @endif value="{{$data->total()}}">All</option>
              </select>
              @if(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
              <?php
              $managers = App\Models\Manager::select('id', 'name', 'type')->with('invoices')->get();
              ?>
              <div class="mx-1">
                <select class="select2 form-select" name="manager" onchange="this.form.submit()" id="">
                  <option>Select Manager</option>
                  @foreach($managers as $m_item)
                  <option @if (request('manager')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{$m_item->type}} | ({{$m_item->invoices->count()}})</option>
                  @endforeach
                </select>
              </div>
              @endif
              <select class="form-control" name="status" onchange="this.form.submit()">
                <option value="">--Status--</option>
                <option @if (request('status')==STATUS_PAID) selected @endif value="{{STATUS_PAID}}">{{STATUS_PAID}}</option>
                <option @if (request('status')==STATUS_DUE) selected @endif value="{{STATUS_DUE}}">{{STATUS_DUE}}</option>
                <option @if (request('status')==STATUS_OVER_PAID) selected @endif value="{{STATUS_OVER_PAID}}">{{STATUS_OVER_PAID}}</option>
                <option @if (request('status')==STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{STATUS_PENDING}}</option>
                <option @if (request('status')==STATUS_ACCEPTED) selected @endif value="{{STATUS_ACCEPTED}}">{{STATUS_ACCEPTED}}</option>
                <option @if (request('status')==STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{STATUS_REJECTED}}</option>
              </select>
            </div>
            <div class="col-md-7 p-0">
              <div class="input-group">
                <select class="form-control" name="invoice_for" onchange="this.form.submit()">
                  <option value="">--Invoice For --</option>
                  <option @if (request('invoice_for')==INVOICE_NEW_USER) selected @endif value="{{INVOICE_NEW_USER}}">{{INVOICE_NEW_USER}}</option>
                  <option @if (request('invoice_for')==INVOICE_DELETE_CUSTOMER) selected @endif value="{{INVOICE_DELETE_CUSTOMER}}">{{INVOICE_DELETE_CUSTOMER}}</option>
                  <option @if (request('invoice_for')==INVOICE_CUSTOMER_ADD_BALANCE) selected @endif value="{{INVOICE_CUSTOMER_ADD_BALANCE}}">{{INVOICE_CUSTOMER_ADD_BALANCE}}</option>
                  <option @if (request('invoice_for')==INVOICE_CUSTOMER_MONTHLY_BILL) selected @endif value="{{INVOICE_CUSTOMER_MONTHLY_BILL}}">{{INVOICE_CUSTOMER_MONTHLY_BILL}}</option>
                  <option @if (request('invoice_for')==INVOICE_MANAGER_ADD_PANEL_BALANCE) selected @endif value="{{INVOICE_MANAGER_ADD_PANEL_BALANCE}}">{{INVOICE_MANAGER_ADD_PANEL_BALANCE}}</option>
                  <option @if (request('invoice_for')==INVOICE_MANAGER_RECEIVED) selected @endif value="{{INVOICE_MANAGER_RECEIVED}}">{{INVOICE_MANAGER_RECEIVED}}</option>
                  <option @if (request('invoice_for')==INVOICE_CONNECTION_FEE) selected @endif value="{{INVOICE_CONNECTION_FEE}}">{{INVOICE_CONNECTION_FEE}}</option>
                </select>
                <input type="search" name="search_query" class="form-control" value="{{request()->search_query}}" placeholder="Search">
                <button type="submit" class="btn btn-outline-primary p-1 fs-tiny">Search</button>
                <a href="{{route(Route::currentRouteName())}}" class="p-1 btn btn-outline-warning fs-tiny">Clear</a>
              </div>
            </div>
          </div>
        </div>
    </form>
    @can('Invoice Add')
    <div class="col-sm-12 col-md-1">
      <a href="{{route('invoice.create')}}" class="btn btn-primary btn-sm">New Invoice</a>
    </div>
    @endcan
  </div>
</div>
<div class="card-datatable table-responsive">
  <table class="datatables-users table border-top">
    <thead>
      <tr>
        <th><i class="fa fa-cogs"></i></th>
        <th>#ID</th>
        <th>invoice_no</th>
        <th>Name</th>
        <th>package</th>
        <th>amount</th>
        <th>Payment Date</th>
        <th>User New Expire date</th>
        <th>status</th>
        <th>received amount</th>
        <th>Manager</th>
        <th>Created date</th>
        <th>Invoice For</th>
        <th>Method</th>
        <th>due amount</th>
        <th>advanced amount</th>
        <th>Comment</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data as $item)
      <tr>
        <td>
          <div class="dropdown">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
            <div class="dropdown-menu">
              <!-- @if($item->customer && $item->status !== STATUS_PAID )
                <a href="{{route('user-change-expire-date', $item->customer->id)}}" class="dropdown-item text-warning">Change Expire Date</a>
                @endif -->
              @can('Invoice Details')
              <a href="{{route('invoice.show', $item->id)}}" class="dropdown-item">View Invoice</a>
              @endcan
              <?php
              $f_data = App\Models\Invoice::with('customer', 'customer.manager')->where('id', $item->id)->first();
              ?>
              @can('Invoice Payments')
              <!-- && (isset($f_data->customer) && $f_data->customer->manager->type == 'app_manager' && auth()->user()->type == 'app_manager') | $f_data->customer->manager->type == 'franchise' && auth()->user()->id == $f_data->customer->manager->id | auth()->user()->hasRole(SUPER_ADMIN_ROLE)) -->
              @if($item->manager_for == auth()->user()->type || auth()->user()->hasRole(SUPER_ADMIN_ROLE))
              @if(($item->status == 'due' || $item->status == 'pending'))
              <a href="{{route('invoice_payment_get', $item->id)}}" class="dropdown-item text-success">Payment</a>
              @endif
              @endif
              @endcan
            </div>
          </div>
        </td>
        <td>{{$item->id}}</td>
        <td><a class="text-{{$item->status == STATUS_PAID ? 'success' : ($item->status == STATUS_DUE ? 'danger' : ($item->status == STATUS_OVER_PAID ? 'success' : ($item->status == STATUS_PENDING ? 'warning' : 'danger'))) }}" href="{{route('invoice.show', $item->id)}}">{{$item->invoice_no}}</a></td>
        @if($item->customer)
        <td><a href="{{ route('customer-user.show', $item->customer->id) }}">{{ $item->customer->username }}</a></td>
        @else
        @if($item->manager)
        @if($item->invoice_for == INVOICE_MANAGER_ADD_PANEL_BALANCE && $item->franchise_manager)
        <td><a href="{{ route('managerProfile', $item->franchise_manager->id) }}">{{ $item->franchise_manager->name }}</a></td>
        @else
        <td><a href="{{ route('managerProfile', $item->manager->id) }}">{{ $item->manager->name }}</a></td>
        @endif
        @endif
        @endif
        <td>{{$item->package ? $item->package->name :''}}</td>
        <td>{{$item->amount}}</td>
        <td> @if($item->status !== STATUS_PENDING) {{$item->updated_at->format('d-m-Y h:i:s a')}} @endif </td>
        <td>{{$item->customer_new_expire_date ?  \Carbon\Carbon::parse($item->customer_new_expire_date)->format('Y-m-d h:i:s A') : ''}}</td>
        <td>
          <span class="text-capitalize badge bg-label-{{ 
        $item->status == STATUS_PAID ? 'success' : 
        ($item->status == STATUS_DUE ? 'danger' : 
        ($item->status == STATUS_PENDING ? 'warning' : 'secondary')) }}">
            {{ $item->status }}
          </span>
        </td>
        <td>{{$item->received_amount}}</td>
        <td>{{$item->manager ? $item->manager->name:'N/A'}}</td>
        <td>{{$item->created_at->format('Y-m-d h:i:s A')}}</td>
        <td> <span class="text-capitalize">{{str_replace('_',' ',$item->invoice_for)}}</span></td>
        <td>{{$item->paid_by}}</td>
        <td>{{$item->due_amount}}</td>
        <td>{{$item->advanced_amount}}</td>
        <td>{{$item->comment}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="ml-4 data_table_pagination">{{ $data->appends(['search_query'=>request('search_query'),'manager'=>request('manager')])->links() }}</div>
</div>
</div>
@endsection