@extends('layouts/layoutMaster')
@section('title', 'Dashboards')
@section('content')
<h4 class="card-title my-3">Dashboard</h4>
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total Customers</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{App\Models\Customer::where('manager_id', auth()->user()->id)->get()->count();}}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Active Customers</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{App\Models\Customer::where(['manager_id' => auth()->user()->id, 'status' => 'active'])->get()->count();}}</h4>
            </div>
          </div>
          <!-- ->whereMonth('created_at', Illuminate\Support\Carbon::now()->month) -->
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Inactive Customers</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{App\Models\Customer::where('status','!=', 'active')->where('manager_id', auth()->user()->id)->get()->count() }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total Wallet Balance</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{auth()->user()->wallet}} TK</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Panel Balance</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{auth()->user()->panel_balance}} TK</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Customer Bill Collected</span>
            <br>
            <small class="text-center">(This Month)</small>
            <div class="d-flex align-items-center my-1">
              <?php

              use App\Models\Invoice;

              $customers =  App\Models\Customer::where('manager_id', auth()->user()->id)->get()->pluck('id');
              $paidinv = Invoice::whereIn('customer_id', $customers)->where('status', 'paid')->whereMonth('created_at', Illuminate\Support\Carbon::now()->month)->get();
              $due_inv = Invoice::whereIn('customer_id', $customers)->where(['status' => 'due', 'status' => 'pending'])->whereMonth('created_at', Illuminate\Support\Carbon::now()->month)->get();
              ?>
              <h4 class="mb-0 me-2">{{$paidinv->sum('amount')}} TK</h4>
            </div>
          </div>
          <h4 class="mb-0 me-2">{{$paidinv->count()}}</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Customer Bill Due</span>
            <br>
            <small class="text-center">(This Month)</small>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$due_inv->sum('amount')}} TK</h4>
            </div>
          </div>
          <!-- due, pending -->
          <h4 class="mb-0 me-2">{{$due_inv->count()}}</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Mikrotik Disabled User</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ App\Models\Customer::where(['manager_id'=>auth()->user()->id,'mikrotik_disabled'=> STATUS_TRUE])->get()->count();}}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="12">
    <div class="col-12">

      <script src="{{asset('assets/js')}}/chart/highcharts.js"></script>
      <script src="{{asset('assets/js')}}/chart/data.js"></script>

      <figure class="highcharts-figure">
        <div id="container"></div>

        <table class="d-none" id="datatable">
          <thead>
            <tr>
              <th>Month</th>
              <th>Income</th>
              <th>Expense</th>
            </tr>
          </thead>
          <tbody>
            @foreach($monthly_report as $f_data)
            <tr>
              @foreach($f_data as $item)
              <th>{{ $item }}</th>
              @endforeach
            </tr>
            @endforeach
          </tbody>
        </table>
      </figure>
      <script type="text/javascript">
        Highcharts.chart('container', {
          data: {
            table: 'datatable'
          },
          chart: {
            type: 'column'
          },
          title: {
            text: 'Monthly Report'
          },
          xAxis: {
            type: 'category'
          },
          yAxis: {
            allowDecimals: false,
            title: {
              text: 'Amount'
            }
          }
        });
      </script>
    </div>
  </div>
</div>
@endsection