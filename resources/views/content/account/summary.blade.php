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

@section('title', 'Account Summary')
@section('content')


<?php

// ====== Total Expance ========
$dailyExpense  = App\Models\DailyExpense::select('amount', 'manager_id', 'manager_for')
  ->when(auth()->user(), function ($q) {
    if (auth()->user()->type == FRANCHISE_MANAGER) {
      return $q->where('manager_id', auth()->user()->id);
    } else {
      return $q->where('manager_for', APP_MANAGER);
    }
  })->sum('amount');


$expance_total_invoice = App\Models\Invoice::when(auth()->user(), function ($q) {
  if (auth()->user()->type == FRANCHISE_MANAGER) {
    return $q->where(['manager_id' => auth()->user()->id, 'invoice_for' => INVOICE_MANAGER_ADD_PANEL_BALANCE]);
  } else {
    return $q->where(['manager_for' => APP_MANAGER, 'invoice_type' => INVOICE_TYPE_EXPENCE]);
  }
})->sum('received_amount');
$totalDailyExpense = $dailyExpense + $expance_total_invoice;

// ====== Total Expance ========



// ====== Total Income Start ========
$totalDailyIncome  = App\Models\DailyIncome::select('amount', 'manager_id', 'manager_for')
  ->when(auth()->user(), function ($q) {
    if (auth()->user()->type == FRANCHISE_MANAGER) {
      return $q->where('manager_id', auth()->user()->id);
    } else {
      return $q->where('manager_for', APP_MANAGER);
    }
  })->sum('amount');


$total_income_invoice =  App\Models\Invoice::when(auth()->user(), function ($q) {
  if (auth()->user()->type == FRANCHISE_MANAGER) {
    return $q->where(['manager_id' => auth()->user()->id, 'invoice_type' => INVOICE_TYPE_INCOME]);
  } else {
    return $q->where(['manager_for' => APP_MANAGER, 'invoice_type' => INVOICE_TYPE_INCOME]);
  }
})->sum('received_amount');
$totalDailyIncome = $totalDailyIncome + $total_income_invoice;

?>
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Net Amount</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$totalDailyIncome -$totalDailyExpense}} TK</h4>
              <!-- <span class="text-success">(+29%)</span> -->
            </div>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="bi bi-currency-dollar"></i>
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
            <span>Total Income </span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$totalDailyIncome}}</h4>
              <!-- <span class="text-success">(+18%)</span> -->
            </div>
          </div>
          <span class="badge bg-label-success rounded p-2">
            <i class="bi bi-currency-dollar"></i>
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
            <span>Total Expenses</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$totalDailyExpense}}
              </h4>
            </div>
          </div>
          <span class="badge bg-label-warning rounded p-2">
            <i class="bi bi-currency-dollar"></i>
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
            <span>Day Of Income</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2"> @php
                $inv = $incomes->filter(function ($income) {
                return $income->invoice_no !== null; // Adjust this condition based on your logic
                })->sum('received_amount');
                $income = $incomes->filter(function ($income) {
                return $income->invoice_no == null; // Adjust this condition based on your logic
                })->sum('amount');
                $daly_income_cal = $inv+$income;
                echo $daly_income_cal;
                @endphp</h4>
            </div>
          </div>
          <span class="badge bg-label-success rounded p-2">
            <i class="bi bi-currency-dollar"></i>
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
            <span>Day Of Expenses</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">
                @php
                $inv = $expenses->filter(function ($income) {
                return $income->invoice_no !== null; // Adjust this condition based on your logic
                })->sum('received_amount');
                $income = $expenses->filter(function ($income) {
                return $income->invoice_no == null; // Adjust this condition based on your logic
                })->sum('amount');
                $daily_expances_cal = $inv+$income;
                echo $daily_expances_cal;
                @endphp
              </h4>
            </div>
          </div>
          <span class="badge bg-label-warning rounded p-2">
            <i class="bi bi-currency-dollar"></i>
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
            <span>Day Of Collection</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $daly_income_cal - $daily_expances_cal}} TK</h4>
            </div>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="bi bi-currency-dollar"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mt-2">
  <div class="card-header d-flex">
    <h5 class="card-title">Summary</h5>
    <form action="{{route('account-summary')}}" method="get">
      <div class="d-flex ml-4">
        <input type="text" name="date_range" value="{{request('date_range')}}" class="form-control w-100" style="min-width: 318px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
        <input type="submit" class="btn btn-xs btn-outline-primary mx-md-2" value="Submit">
        <a class="btn btn-xs btn-warning" href="{{route('account-summary')}}">Reset</a>
      </div>
    </form>
  </div>
  <div class="card-body">
    <div class="row">
      <!-- if($income->count()+$expenses->count() > 0 ) -->
      <h5 class="col-sm-12 col-md-12 text-center "> <span class="bg-body p-2"> {{request('date_range') ? request('date_range') : 'Today'}} </h5>
      <h5 class="col-sm-12 col-md-12 text-center "> <span class="bg-body p-2"> {{request('date_range') ? 'Total' :'Current'}} Amount
          <span class="text-{{ $daly_income_cal - $daily_expances_cal > 0 ? 'success' : 'warning'}}"> {{ $daly_income_cal - $daily_expances_cal}} TK</span></span>
        <a href="{{route('account-summary-pdf',['date_range'=>request('date_range')])}}">Download pdf</a>
      </h5>
      <!-- endif -->
      <div class="col-sm-12 col-md-6 p-0" style="margin-right:-1px">
        <div class="card-datatable table-responsive border">
          <h5 class="p-2 bg-body">Income ({{$incomes->count()}}) <span class="text-success float-end">Total: {{$incomes->sum('amount')}} TK</span> </h5>
          <table class="datatables-users table">
            <style>
              /* table tr,
              table td,
              table th {
                padding: 0px 5px !important
              } */
            </style>
            <thead>
              <tr>
                <th>NO</th>
                <th>service name</th>
                <th>Category</th>
                <th>Manager Name</th>
                <th>Amount (TK)</th>
                <th>Paid By</th>
              </tr>
            </thead>
            <tbody>
              <!-- income  -->
              @foreach($incomes as $index=>$income_item)
              <tr>
                <td>{{$index+1}}</td>
                <td>{{$income_item->service_name}}</td>
                <td>
                  @if($income_item->category_id !==null)
                  <?php
                  $category =  App\Models\AccountCategory::select('id', 'name')->where('id', $income_item->category_id)->first();
                  if ($category) {
                    echo $category->name;
                  } else {
                    echo 'N/A';
                  }
                  ?>
                  @else
                  N/A
                  @endif
                </td>
                <td>{{$income_item->manager ? $income_item->manager->name :"N/A"}}</td>
                <td>{{$income_item->amount}}</td>
                <td>{{$income_item->method ? $income_item->method : $income_item->paid_by}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 p-0">
        <div class="card-datatable table-responsive border">
          <h5 class="p-2 bg-body">Expenses ({{$expenses->count()}}) <span class="text-warning float-end">Total: {{$daily_expances_cal}} TK</span></h5>
          <style>
            /* td,
            th {
              padding: 2px !important;
            } */
          </style>
          <table class="datatables-users table summart_table">
            <thead>
              <tr>
                <th>NO</th>
                <th>Claimant Name</th>
                <th>Category</th>
                <th>Manager Name</th>
                <th>Amount (TK)</th>
                <th>Paid By</th>
              </tr>
            </thead>
            <tbody>
              <!-- Expances  -->
              @foreach($expenses as $index=>$expenses_item)
              <tr>
                <td>{{$index+1}}</td>
                <td>{{$expenses_item->expense_claimant}}</td>
                <td>
                  @if($expenses_item->category_id !==null)
                  <?php
                  $category =  App\Models\AccountCategory::select('id', 'name')->where('id', $expenses_item->category_id)->first();
                  if ($category) {
                    echo $category->name;
                  }
                  ?>
                  @endif
                </td>
                <td>{{$expenses_item->manager ? $expenses_item->manager->name :"N/A"}}</td>
                <td>{{$expenses_item->received_amount ? $expenses_item->received_amount: $expenses_item->amount}}</td>
                <td>{{$expenses_item->method ? $expenses_item->method : $expenses_item->paid_by }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection