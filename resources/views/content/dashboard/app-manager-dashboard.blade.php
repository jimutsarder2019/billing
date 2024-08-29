@extends('layouts/layoutMaster')
@section('title', 'Dashboards')
@section('content')
<style>
  .icone_size_40 {
    font-size: 40px;
  }
</style>
<div class="card">
  <div class="card-body">
    <h4 class="card-title my-3">DASHBOARD
    </h4>
    <div class="row g-4 mb-4">
      @if(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <div class="d-flex align-items-center my-1">
                  <span class="badge bg-label-primary p-2">
                    <i class="ti ti-users icone_size_40"></i>
                  </span>
                </div>
              </div>
			  <div class="content">
                <p class="mb-0">Total User <span class="text-success visible_area d-none">({{App\Models\Customer::get()->count()}})</span></p>
                <p class="mb-0">Franchise <span class="text-success visible_area d-none"> ({{App\Models\Customer::where('customer_for',FRANCHISE_MANAGER)->get()->count()}}) </span></p>
                <p class="mb-0">App Manager <span class="text-success visible_area d-none">({{App\Models\Customer::where('customer_for',APP_MANAGER)->get()->count()}}) </span></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
			    <span class="badge bg-label-success p-2">
                  <i class="ti ti-user-check icone_size_40"></i>
                </span>
              </div>
              <div class="d-flex align-items-center my-1">
                  <p>Total Active <span class="visible_area d-none text-success">({{auth()->user()->hasRole(SUPER_ADMIN_ROLE) ? App\Models\Customer::where('status','=', 'active')->get()->count() : App\Models\Customer::where('status','!='. 'active')->get()->count() }})</span></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      @if(!auth()->user()->hasRole(SUPER_ADMIN_ROLE))
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="badge bg-label-success p-2">
                  <i class="ti ti-user-check icone_size_40"></i>
                </span>
                <span>Today Expiring User</span>
              </div>
              <div class="d-flex align-items-center my-1 ">
                <h4 class="mb-0 me-2">{{$today_expiring_customers}}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="badge bg-label-danger p-2">
                  <i class="ti ti-user-x icone_size_40"></i>
                </span><br>
                <span>Expired User</span>
              </div>
              <div class="d-flex align-items-center my-1">
                <?php
                $ttl_count = App\Models\Customer::where('status', 'expire')->get()->count() - App\Models\Customer::where('mikrotik_disabled', STATUS_TRUE)->get()->count();
                ?>
                <h4 class="mb-0 me-2">{{$ttl_count>0 ? $ttl_count :0}}</h4>
              </div>
            </div>
          </div>
        </div>

      </div>
      @if(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="badge bg-label-success p-2">
                  <i class="ti ti-users icone_size_40"></i>
                </span><br>
                <span>New Customers</span><small>(This month)</small>
              </div>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{App\Models\Customer::where('status','active')->whereMonth('connection_date', Illuminate\Support\Carbon::now()->month)->whereYear('connection_date', Illuminate\Support\Carbon::now()->year)->count()}}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="badge bg-label-success p-2">
                  <i class="ti ti-file icone_size_40"></i>
                </span><br>
                <span>Total Invoice</span>
              </div>
              <div class="">
                <div class="d-flex align-items-center">
                  <?php
                  $total_inv_amount =   auth()->user()->hasRole(SUPER_ADMIN_ROLE) ? App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'created_at')->where(['status' => STATUS_PAID, 'manager_for' => APP_MANAGER])
                    ->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)
                    ->get()->sum('received_amount') :
                    App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'created_at')->where(['status' => 'paid', 'manager_id' => auth()->user()->id])
                    ->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)
                    ->whereMonth('created_at', Illuminate\Support\Carbon::now()->month)
                    ->get()->sum('received_amount')
                  ?>
                  <h6 class="align-items-end visible_area d-none">{{$total_inv_amount}} TK</h6>
                </div>
                <span class="rounded">
                  <?php
                  $ttl_inv = auth()->user()->hasRole(SUPER_ADMIN_ROLE) ? App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'created_at')->where(['status' => STATUS_PAID, 'manager_for' => APP_MANAGER])
                    ->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)
                    ->get()->count() :
                    App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'created_at')->where(['status' => 'paid', 'manager_id' => auth()->user()->id])
                    ->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)
                    ->whereMonth('created_at', Illuminate\Support\Carbon::now()->month)->get()->count()
                  ?>
                  <h4 class="mb-0 me-2">{{$ttl_inv}}</h4>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      @if(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="badge bg-label-success p-2">
                  <i class="ti ti-file icone_size_40"></i>
                </span><br>
                <span>This Month Collected</span>
              </div>
              <div class="align-items-center my-1">
                <?php
                $this_month_amount = App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'created_at')->where(['status' => STATUS_PAID, 'manager_for' => APP_MANAGER])->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)->whereMonth('updated_at', Illuminate\Support\Carbon::now()->month)->sum('received_amount')
                ?>
                <h6 class="mb-0 me-2 visible_area d-none">{{$this_month_amount}} TK</h6>
                <?php
                $this_month_total = App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'created_at')->where(['status' => STATUS_PAID, 'manager_for' => APP_MANAGER])->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)->whereMonth('updated_at', Illuminate\Support\Carbon::now()->month)->count()
                ?>
                <h4 class="mb-0 me-2">{{$this_month_total}}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="badge bg-label-success p-2">
                  <i class="ti ti-file icone_size_40"></i>
                </span><br>
                <span>Last Month Collection</span>
              </div>
              <div class="align-items-center my-1">
                <?php
                $now = Illuminate\Support\Carbon::now();
                echo $now->subMonth();
                $last_month_amount = App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'updated_at')->where(['status' => STATUS_PAID, 'manager_for' => APP_MANAGER])
                  ->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)->whereMonth('updated_at', Illuminate\Support\Carbon::now()->subMonth())->sum('received_amount')
                ?>
                <h6 class="mb-0 me-2">{{$last_month_amount}} TK</h6>
                <?php
                $_month_total = App\Models\Invoice::select('id', 'status', 'manager_for', 'invoice_for', 'received_amount', 'updated_at')
                  ->where(['status' => STATUS_PAID, 'manager_for' => APP_MANAGER])->where('invoice_for', '!=', INVOICE_CUSTOMER_ADD_BALANCE)->whereMonth('updated_at', Illuminate\Support\Carbon::now()->subMonth())->count()
                ?>
                <h4 class="mb-0 me-2">{{$_month_total}}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif

      @if(!auth()->user()->hasRole(SUPER_ADMIN_ROLE))
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span>Transferred Amount</span>
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ App\Models\ManagerBalanceTransferHistory::where('sender_id', auth()->user()->id)->whereMonth('created_at', Illuminate\Support\Carbon::now()->month)->get()->count();}}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span>Wallet</span>
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{auth()->user()->wallet}}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      @if(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
			    <span class="badge bg-label-info p-2">
                  <i class="ti ti-brand-finder icone_size_40"></i>
                </span>
              </div>
              <div class="align-items-center my-1">
                <h6 class="mb-0 me-2">Managers</h6></br
                <p class="mb-0">App Managers <span class="text-success">({{ App\Models\Manager::where('type', 'app_manager')->get()->count();}})</span></p>
                <p>Franchise <span class="text-success">({{ App\Models\Manager::where('type', 'franchise')->get()->count();}}) </span></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif

      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body card-body-2">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="badge bg-label-primary p-2">
                  <i class="ti ti-server icone_size_40"></i>
                </span><br>
                <span>Mikrotik Disabled User</span>
              </div>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ App\Models\Customer::where('mikrotik_disabled', STATUS_TRUE)->get()->count();}}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
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
</div>
@endsection


@push('pricing-script')
<script>
  $(document).ready(function() {
    $('.toggleButton').click(function() {
      $('.visible_area').toggleClass('d-none');
    });
  });
</script>
@endpush