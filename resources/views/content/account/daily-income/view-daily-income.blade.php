@extends('layouts/layoutMaster')
@section('title','Daily Income')

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
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Income</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">
                {{$total_amount}} TK
              </h4>
              <!-- <span class="text-success">(+29%)</span> -->
            </div>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            {{$incomes->total()}}
          </span>
        </div>
      </div>
    </div>
  </div>
  @foreach($card_data as $c_item)
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>{{$c_item['method']}}</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">
                {{$c_item['sum']}} TK
              </h4>
              <!-- <span class="text-success">(+29%)</span> -->
            </div>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            {{$c_item['count']}}
          </span>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<div class="card">
  <h5 class="card-title mt-2  ms-2">Daily Income</h5>
  <div class="card-header d-md-flex p-1">
    <div class="row">
      <div class="col-md-10 col-sm-12">
        <form action='{{route("account-daily-income")}}' class=" d-md-flex">
          <select class="form-control" name="item" onchange="this.form.submit()" id="" style="width: 100px;">
            <option @if ($incomes->count() == '10') selected @endif value="10">10</option>
            <option @if ($incomes->count() == '50') selected @endif value="50">50</option>
            <option @if ($incomes->count() == '100' ) selected @endif value="100">100</option>
            <option @if ($incomes->count() == $incomes->total()) selected @endif value="{{$incomes->total()}}">All({{$incomes->total()}})</option>
          </select>
          @if(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
          <?php
          $managers = App\Models\Manager::select('id', 'name', 'type')->with('invoices')->get();
          ?>
          <div class="mx-1">
            <select class="select2 form-select" name="manager" onchange="this.form.submit()" id="">
              <option value="">Select a Manager</option>
              @foreach($managers as $m_item)
              <option @if (request('manager')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{$m_item->type}} | ({{$m_item->invoices->count()}})</option>
              @endforeach
            </select>
          </div>
          @endif
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
          </div>
          <div class="form-group d-flex">
            <input type="text" name="date_range" value="{{request('date_range')}}" class="form-control w-100" style="min-width: 318px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
            <input type="submit" class="btn btn-xs btn-outline-primary" value="Submit">
            <a href="{{route('account-daily-income')}}" class="btn btn-xs btn-warning mx-1">Clear</a>
            <a href="{{route('account-daily-income',['export'=>'income', 'date_range'=>request('date_range'),'item'=>$incomes->total()])}}" class="btn btn-xs btn-success">Export xlsx</a>
          </div>
        </form>
      </div>
      <div class="col-md-2 col-sm-12">
        <div class="">
          @can('Daily-Income Add')
          <button data-bs-toggle="modal" data-bs-target="#addIncomeModal" class="btn btn-primary">Add Income</button>
          @include('content/account/daily-income/add-daily-income-modal')
          @endcan
        </div>
      </div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th><i class="fa fa-cogs"></i></th>
          <th>SL No.</th>
          <th>username Or service name</th>
          <th>Date</th>
          <th>Received Date</th>
          <th>Voucher or Invoice NO</th>
          <th>Category</th>
          <th>Amount</th>
          <th>Method</th>
          <th>Transaction Id</th>
          <th>Received By</th>
        </tr>
      </thead>
      <tbody>
        @foreach($incomes as $income)
        <tr>
          <td>
            @if($income->vouchar_no)
            @can('Daily-Income Edit')
            <div class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editIncomeModal"><i class="bi bi-pencil-square"></i> </div>
            @include('content/account/daily-income/add-daily-income-modal', ['editData'=>$income])
            @endcan
            @can('Daily-Income Delete')
            <a class="btn btn-sm btn-danger" onclick="return confirm('are you sure to delete')" href="{{route('dailyIncomeDelete', $income->id)}}"><i class="bi bi-trash"></i></a>
            @endcan
            @endif
          </td>
          <td>{{$income->id}}</td>
          <td>{{$income->customer ? $income->customer->username:'' }} {{$income->service_name }} </td>
          <td>{{$income->date ?? 'NA'}}</td>
          <td>{{$income->updated_at->format('d-m-Y h:i:s a') ?? 'NA'}}</td>
          <td>{{$income->vouchar_no ?? $income->invoice_no}}</td>
          <td>
            @if($income->category_id !==null)
            <?php
            $category =  App\Models\AccountCategory::select('id', 'name')->where('id', $income->category_id)->first();
            if ($category) {
              echo $category->name;
            } else {
              echo $income->category_id;
            }
            ?>
            @else
            N/A
            @endif
          </td>
          <td>{{$income->amount}}</td>
          <td>{{$income->method}}</td>
          <td>{{$income->transaction_id}}</td>
          <td>{{$income->manager ? $income->manager->name : ''}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $incomes->appends(['item'=>request('item'),'date_range'=>request('date_range')])->links() }}</div>
  </div>
</div>
@endsection