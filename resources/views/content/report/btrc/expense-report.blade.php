@extends('layouts/layoutMaster')
<?php
$route = 'reprot.expense_report';
$title = 'Expense Report';
?>
@section('title', $title)
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}" />

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.6/css/dataTables.dataTables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>

<!-- vendor scru -->
<script src="https://cdn.datatables.net/2.0.6/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

@endsection
@section('page-script')
<script src="{{asset('assets/js/forms-pickers.js')}}"></script>

<script>
  new DataTable('#example', {
    layout: {
      topStart: {
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
      },
      topEnd: null,
      bottomStart: null,
      bottomEnd: null,
    },
    "paging": false
  });
</script>

@endsection
@section('content')
<div class="card">
  <div class="card-header py-1">
    <h5 class="card-title">{{$title}}</h5>

    <form action='{{route("reprot.expense_report")}}' class="">
      <div class="row">
        <div class="col-sm-3 col-md-1 px-0">
          <select class="form-control mr-1" name="item" onchange="this.form.submit()" id="">
            <option @if ($data->count() == '10') selected @endif value="10">10</option>
            <option @if ($data->count() == '50') selected @endif value="50">50</option>
            <option @if (request('item')=='all' ) selected @endif value="all">All</option>
          </select>
        </div>
        <div class="col-sm-3 col-md-3">
          <div class="form-group d-flex">
            <input type="text" name="date_range" value="{{request('date_range')}}" class="form-control w-100" style="min-width: 318px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
          </div>
        </div>

        <div class="col-md-3 px-0">
          <div class="input-group">
            @if(auth()->user()->type !== FRANCHISE_MANAGER)
            <?php
            $managers = App\Models\Manager::select('id', 'name', 'type')->with('customers')->get();
            ?>
            <select class="select2 form-select" name="manager" onchange="this.form.submit()" id="">
              <option>Select Manager</option>
              @foreach($managers as $m_item)
              <option @if (request('manager')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{$m_item->type}}</option>
              @endforeach
            </select>
            @endif
            <select name="payment_method" class="form-control me-1" id="" onchange="this.form.submit()">
              <option value="">Payment Method</option>
              <option {{ request('payment_method')  == CASH_METHOD ?'selected':'' }} value="{{CASH_METHOD}}">{{CASH_METHOD}}</option>
              <option {{ request('payment_method')  == BKASH_METHOD ?'selected':'' }} value="{{BKASH_METHOD}}">{{BKASH_METHOD}}</option>
              <option {{ request('payment_method')  == BANK_METHOD ?'selected':'' }} value="{{BANK_METHOD}}">{{BANK_METHOD}}</option>
            </select>
          </div>
        </div>
        <div class="col-md-3 px-0">
          <div class="btn-group">
            <input type="search" name="search_query" class="form-control" value="{{request()->search_query}}" placeholder="Search" id="">
            <input type="submit" class="btn btn-xs btn-outline-primary" value="Submit">
            <a href="{{route(Route::currentRouteName())}}" class="btn btn-outline-warning">Clear</a>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div class="card-datatable table-responsive">
    <table id="example" class="display nowrap datatables-users table">
      <thead>
        <tr>
          <th class="text-center">SL</th>
          <th class="text-center">Payment Date</th>
          <th class="text-center">amount</th>
          <th class="text-center">received</th>
          <th class="text-center">method</th>
          <th class="text-center">manager</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $index=>$item)
        <tr>
          <td class="text-center">{{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}</td>
          <td class="text-center">{{$item->updated_at ? $item->updated_at->format('d-m-Y h:i:s a') :'N/A'}} </td>
          <td class="text-center">{{$item->amount ? $item->amount :'00'}}</td>
          <td class="text-center">{{$item->received_amount ? $item->received_amount :'00'}}</td>
          <td class="text-center">{{$item->method}}</td>
          <td class="text-center">{{$item->manager ? $item->manager->name:'N/A'}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $data->appends(['date_range'=>request('date_range'),'payment_method'=>request()->payment_method,'search_query'=>request()->search_query,'manager'=>request('manager')])->links() }}</div>
  </div>
</div>
@endsection