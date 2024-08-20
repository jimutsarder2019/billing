<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Summary
    @php
    echo isset($req_date_range ) & $req_date_range !== null? $req_date_range : Carbon\Carbon::now()->format('d-m-Y');
    @endphp
  </title>
</head>

<body>
  <style>
    @page {
      margin: 0px;
    }

    @media print {
      .table_area .table_area-income {
        margin-right: -1px !important;
      }
    }


    body {
      margin: 0;
      padding: 0;
    }

    .header_section_bg {
      background: #f3f3f3;
    }

    .datatables-css-style {
      font-family: Arial, Helvetica, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }

    .datatables-css-style td,
    .datatables-css-style th {
      border: 1px solid #ddd;
      padding: 8px;
      font-size: 10px;
    }

    .datatables-css-style tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    .datatables-css-style tr:hover {
      background-color: #ddd;
    }

    .datatables-css-style th {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: left;
      background-color: #04AA6D;
      color: white;
    }

    .table_area {
      width: 49.95%;
    }

    .w-50 {
      width: 50%;
    }

    .float-end {
      float: right !important;
    }

    .title_header {
      text-align: center !important;
    }

    .float-start {
      float: left !important;
    }

    /* .table_area-income {
      margin-right: -1px;
    } */

    .bg-body {
      background: #f8f7fa !important;
    }

    .p-2 {
      padding: 0.5rem !important;
    }

    h5 {
      margin: 0px !important;
    }

    .mt-3 {
      margin-top: 3rem;
    }

    .table_area-total {
      float: right;
      margin-top: 5px;
      padding: 5px;
    }

    .mt-0 {
      margin-top: 0px !important;
    }

    .mb-0 {
      margin-bottom: 0px !important;
    }

    .m-0 {
      margin: 0px !important;
    }

    .d-flex {
      display: flex;
    }

    .text-end {
      text-align: end !important;
    }

    .text-center {
      text-align: center !important;
    }

    .table_area .table_area-income {
      margin-right: -1px !important;
    }
  </style>


  @php
  $inv = $expenses->filter(function ($income) {
  return $income->invoice_no !== null; // Adjust this condition based on your logic
  })->sum('received_amount');
  $income = $expenses->filter(function ($income) {
  return $income->invoice_no == null; // Adjust this condition based on your logic
  })->sum('amount');
  $daily_expances_cal = $inv+$income;
  $daily_expances_cal;
  @endphp


  @php
  $inv = $incomes->filter(function ($income) {
  return $income->invoice_no !== null; // Adjust this condition based on your logic
  })->sum('received_amount');
  $income = $incomes->filter(function ($income) {
  return $income->invoice_no == null; // Adjust this condition based on your logic
  })->sum('amount');
  $daly_income_cal = $inv+$income;
  $daly_income_cal;
  @endphp
  <div class="w-100">
    <div class="header_section_bg">
      <h1 class="title_header mt-0 mb-0">Account Summary</h1>
      <h5 class="title_header mb-0"> {{$req_date_range ? $req_date_range : 'Today'}}</h5>
      <p class="title_header mb-0"><strong>Total:</strong> {{$incomes->sum('amount') - $expenses->sum('amount')}} TK</p>
    </div>
    <div class="w-100" style="display: flex;">
      <div class="p-0 table_area table_area-income float-start" style="margin-right: -1px;">
        <div class="card-datatable table-responsive border">
          <p class="p-2 m-0 bg-body text-center"><strong>Income:</strong> <small>{{$incomes->sum('amount')}} TK of ({{$incomes->count()}} Items)</small></p>
          <table class="datatables-css-style table">
            <thead>
              <tr>
                <th>NO</th>
                <th>Service Name</th>
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
                <td>{{$income_item->manager ? $income_item->manager->name :"N/A"}}</td>
                <td>{{$income_item->amount}}</td>
                <td>{{$income_item->method ? $income_item->method : $income_item->paid_by}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <div class="table_area p-0 float-end">
        <div class="card-datatable table-responsive border">
          <p class="p-2 m-0 bg-body text-center"><strong>Expenses:</strong> <small>{{$expenses->sum('amount')}} TK of ({{$expenses->count()}} Items)</small></p>
          <table class="datatables-css-style table summart_table">
            <thead>
              <tr>
                <th>NO</th>
                <th>Service Name</th>
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
</body>

</html>