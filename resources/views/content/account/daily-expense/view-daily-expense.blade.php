@extends('layouts/layoutMaster')
@section('title','Daily Expanses')

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
            <span>Net Amount</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$total_amount}} TK</h4>
            </div>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            {{$expenses->total()}}
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
  <h5 class="card-title ms-1 mt-1">Daily Expenses</h5>
  <div class="card-header d-md-flex p-1">
    <form action='{{route("account-daily-expenses")}}' class="d-flex">
      <select class="form-control" name="item" onchange="this.form.submit()" id="" style="width: 100px;">
        <option @if ($expenses->count() == '10') selected @endif value="10">10</option>
        <option @if ($expenses->count() == '50') selected @endif value="50">50</option>
        <option @if ($expenses->count() == '100' ) selected @endif value="100">100</option>
        <option @if ($expenses->count() == $expenses->total()) selected @endif value="{{$expenses->total()}}">All</option>
      </select>
      <div class="btn-group d-flex">
        <input type="text" name="date_range" value="{{request('date_range')}}" class="form-control w-100" style="min-width: 318px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
        <input type="submit" class="btn btn-xs btn-outline-primary" value="Submit">
        <a href="{{route('account-daily-expenses')}}" class="btn btn-xs btn-warning mx-1">Clear</a>
        <a href="{{route('account-daily-expenses',['export'=>'expenses', 'date_range'=>request('date_range'), 'item'=>$expenses->total()])}}" class="btn btn-xs btn-success">Export xlsx</a>
      </div>
    </form>
    <div class="mx-md-2">
      @can('Daily-Expenses Add')
      <button data-bs-toggle="modal" data-bs-target="#createModal" class="btn btn-primary btn-xs">Add Expense</button>
      @include('content/account/daily-expense/add-daily-expense-modal')
      @endcan
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Date</th>
          <th>Claimant Name</th>
          <th>Category</th>
          <th>Amount</th>
          <th>Method</th>
          <th>Transaction Id</th>
          <th>Creator Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($expenses as $expense)
        <tr>
          <td>{{$expense->id}}</td>
          <td>{{$expense->date ?? $expense->created_at}}</td>
          <td>{{$expense->expense_claimant ?? $expense->invoice_for}}</td>
          <td>
            @if($expense->category_id !==null)
            <?php
            $category =  App\Models\AccountCategory::select('id', 'name')->where('id', $expense->category_id)->first();
            if ($category) {
              echo $category->name;
            }
            ?>
            @endif
            <!-- $expense->category_id -->
          </td>
          <td>{{$expense->received_amount ? $expense->received_amount: $expense->amount}}</td>
          <td>{{$expense->method ?? $expense->paid_by}}</td>
          <td>{{$expense->transaction_id}}</td>
          <td>{{$expense->manager ? $expense->manager->name : ''}}</td>
          <td class="">
            @if($expense->vouchar_no)
            @can('Daily-Expenses Edit')
            <div class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal-{{$expense->id}}"><i class="bi bi-pencil-square"></i> </div>
            @endcan
            @can('Daily-Expenses Delete')
            <a onclick="return confirm('are you sure to delete')" class="btn btn-sm btn-danger" href="{{route('dailyExpenceDelete', $expense->id)}}"><i class="bi bi-trash"></i></a>
            @endcan
            @endif
          </td>
        </tr>
        @include('content/account/daily-expense/add-daily-expense-modal', ['editData'=>$expense])
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $expenses->appends(['item'=>request('item'),'date_range'=>request('date_range')])->links() }}</div>
  </div>
</div>
@endsection