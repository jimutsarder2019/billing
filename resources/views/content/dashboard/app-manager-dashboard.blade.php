@extends('layouts/layoutMaster')
@section('title', 'Dashboards')
@section('content')
<style>
  .icone_size_40 {
    font-size: 40px;
  }
</style>

<div class="card mb-3">
<div class="card-body">
            <div class="row g-4 mb-4">
                <div class="col-sm-12 col-md-12">
                    <form action="{{ route('dashboard.index') }}" method="get" class="">
                        <div class="row">
						    <h4 class="card-title my-3">
							    QUICK ACCESS
							</h4>
                            <div class="col-10">
                                <select name="username" id="" class="select2 form-select"
                                    onchange="this.form.submit()">
                                    <option value="">----Select-----</option>
                                    <?php
                                    $customer = App\Models\Customer::select('id', 'username', 'phone')->get();
                                    ?>
                                    @foreach ($customer as $c_item)
                                        <option {{ request('username') == $c_item->username ? 'selected' : '' }}
                                            value="{{ $c_item->username }}">{{ $c_item->username }} | {{ $c_item->phone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <!-- <input type="text" class="form-control" placeholder="Search username" value="{{ request('username') }}" name="username" aria-label="Recipient's username" aria-describedby="button-addon2"> -->
                                <div class="d-flex">
                                    <!-- <button class="btn btn-outline-primary" type="submit" id="button-addon2">Search</button> -->
                                    <a href="{{ route('dashboard.index') }}" class="btn btn-outline-warning">Clear</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if ($last_logged_out || $uptime || $caller_id || $status)
                        <div class="border p-2 mt-2">
                            @if ($last_logged_out)
                                <strong>Last logged Out : </strong> <span>{{ $last_logged_out }}</span>
                                <br>
                            @endif
                            @if ($uptime)
                                <strong>Uptime : </strong> <span>{{ $uptime }}</span>
                                <br>
                            @endif
                            @if ($caller_id)
                                <strong>Caller Id: </strong>
                                <span>{{ $caller_id ?? 'null' }}</span>
                                <br>
                            @endif
                            @if ($ip_address)
                                <strong>IP Address: </strong>
                                <span>{{ $ip_address ?? 'null' }}</span>
                                <br>
                            @endif
                            @if ($status)
                                <strong>Status: </strong>
                                <span
                                    class="badge bg-label-{{ $status == 'online' ? 'success' : 'warning' }}">{{ $status }}</span>
                                <a href="{{ route('mikrotik_info', ['username' => request('username'), 'id' => $mikrotik_id]) }}"
                                    class="btn btn-sm btn-primary">Live Graph</a>
                                @if ($status == 'online')
                                    <a class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure to disconnect it')"
                                        href="{{ route('mikrotik-online-disconnect', ['id' => $data->mikrotik_id, 'name' => $data['username']]) }}"
                                        title="Disconnect User"><i class="bi bi-x-lg"></i></a>
                                @endif
                                @if ($data->status)
                                    <br>
                                    <strong>Billing Status: </strong>
                                    <span
                                        class="badge bg-label-{{ $data->status == 'active' ? 'success' : 'warning' }} text-catitalize">{{ $data->status }}</span>
                                    <br>
                                @endif
                                <strong>Package: </strong>
                                <span class="text-info text-catitalize">{{ $data->package->name }}</span>
                                @if ($data->expire_date)
                                    <div class="mt-2">
                                        <strong>Expire Date: </strong>
                                        <span
                                            class="text-primary">{{ \Carbon\Carbon::parse($data->expire_date)->format('Y-m-d h:i:s a') }}</span>
                                    </div>
                                @endif
                                @if ($data->allow_grace)
                                    <div class="mt-2">
                                        <strong>Grace: </strong>
                                        <span>{{ $data->allow_grace ?? 0 }} Days</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                    <!-- if(auth()->user()->hasRole(SUPER_ADMIN_ROLE) && $data ) -->
                    @if ($data)
                        @can('update_customer_info')
                            <a href="{{ route('customer-edit-super-manager', $data['id']) }}"
                                class="btn btn-success mt-2">Update Customer Info</a>
                        @endcan
                    @endif
                </div>

                @if ($invoices && $invoices->status == STATUS_PENDING)
                    <div class="col-sm-12 col-md-8">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>INV</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th><i class="fa fa-cogs"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="text-{{ $invoices->status == STATUS_PAID ? 'success' : ($invoices->status == STATUS_DUE ? 'danger' : ($invoices->status == STATUS_OVER_PAID ? 'success' : ($invoices->status == STATUS_PENDING ? 'warning' : 'danger'))) }}">
                                        <td>{{ $invoices->invoice_no }}</td>
                                        <td>{{ $invoices->amount }}</td>
                                        <td>{{ $invoices->status }}</td>
                                        <td>{{ $invoices->created_at->format('d-m-y h:i A') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                    data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                                <div class="dropdown-menu">
                                                    @can('Invoice Payments')
                                                        <a href="{{ route('invoice_payment_get', $invoices->id) }}"
                                                            class="dropdown-item cursor-pointer btn-success">Payment</a>
                    @endif
                    @can('Invoice Edit')
                        @if (auth()->user()->hasRole(SUPER_ADMIN_ROLE))
                            <a href="{{ route('invoice.edit', $invoices->id) }}"
                                class="dropdown-item cursor-pointer btn-warning">Edit Invoice</a>
                        @endcan
                    @endif
                </div>
            </div>
            </td>
            </tr>
            </tbody>
            </table>
        </div>
        </div>
        @endif
        </div>
        </div>
</div>
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