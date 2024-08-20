@extends('layouts/layoutMaster')
@section('title') {{$data->name}} @endsection
@section('content')
<h4 class="fw-bold py-3  card card-body">
  <span class="text-muted fw-light">User / View / {{$data->name}}</span>
</h4>
<div class="row">
  <!-- User Sidebar -->
  <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
    <!-- User Card -->
    <div class="card mb-2">
      <div class="card-body">
        <div class="user-avatar-section">
          <div class=" d-flex align-items-center flex-column">
            <img class="img-fluid rounded mb-3 pt-1 mt-4" src='{{ $data->profile_photo_url ?  asset($data->profile_photo_url) : asset(MANAGER_DEFAULT_LOG) }}' height="100" width="100" alt="{{$data->profile_photo_url}}" />
            <!-- <img class="img-fluid rounded mb-3 pt-1 mt-4" src="{{ asset('assets/img/avatars/15.png') }}" height="100" width="100" alt="User avatar" /> -->
            <div class="user-info text-center">
              <h4 class="mb-2">{{$data->name}}</h4>
              <span class="badge bg-label-secondary mt-1">Roles : {{ str_replace(array('[',']', '"'),'', $data->roles->pluck('name')) }}</span>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-around flex-wrap pt-3 pb-4 border-bottom">
          <div class="d-flex align-items-start me-4 mt-3 gap-2">
            <span class="badge bg-label-primary p-2 rounded"><i class='ti ti-checkbox ti-sm'></i></span>
            <div>
              <p class="mb-0 fw-semibold">{{$data->wallet ?? 00}}</p>
              <small>wallet</small>
            </div>
          </div>
          @if($data->type == FRANCHISE_MANAGER)
          <div class="d-flex align-items-start me-4 mt-3 gap-2">
            <span class="badge bg-label-primary p-2 rounded"><i class='ti ti-checkbox ti-sm'></i></span>
            <div>
              <p class="mb-0 fw-semibold">{{$data->panel_balance ?? 00}}</p>
              <small>Panel Balance</small>
            </div>
          </div>
          @endif
        </div>
        <p class="mt-4 small text-uppercase text-muted">Details</p>
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Type:</span>
              <span class="text-capitalize">{{str_replace('_',' ',$data->type)}}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Email:</span>
              <span>{{$data->email}}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Contact:</span>
              <span>{{$data->phone}}</span>
            </li>

            @if(auth()->user()->type == APP_MANAGER)
            @if(isset($data->mikrotik))
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Mikrotik:</span>
              <span>{{isset($data->mikrotik) ? $data->mikrotik->identity  :'' }} | {{$data->mikrotik ? $data->mikrotik->host :''}} </span>
            </li>
            @endif

            @endif
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Grace Allow:</span>
              {{$data->grace_allowed ?? 0}} Days
            </li>
            @if(count($data->assingZones) > 0)
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Zones:</span>
              @foreach($data->assingZones as $zone)
              <span class="badge  bg-label-info">{{$zone->zone->name}}</span>
              @endforeach
            </li>
            @endif
            <?php
            $assignedSubzones =  App\Models\ManagerAssignSubZone::with('subzone')->where('manager_id', $data->id)->get();
            ?>
            @if(count($assignedSubzones) > 0)
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Sub Zones:</span>
              @foreach($assignedSubzones as $sub)
              <span class="badge  bg-label-info">{{$sub->subzone ? $sub->subzone->name :'N/A'}}</span>
              @endforeach
            </li>
            @endif

          </ul>

        </div>
      </div>
    </div>
  </div>
  <!--/ User Sidebar -->

  <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
    <!-- Invoice table -->
    <div class="card mb-4">
      <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link {{$errors->any() ? '':'active'}}" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice-tab-pane" type="button" role="tab" aria-controls="invoice-tab-pane" aria-selected="true">Invoices</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Balance History</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{$errors->any() ? 'active' :''}}" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-tab-pane" type="button" role="tab" aria-controls="password-tab-pane" aria-selected="false">Security</button>
          </li>
          @if($data->type == FRANCHISE_MANAGER)
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">Assign Packages</button>
          </li>
          @endif
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Change Profile</button>
          </li>

          <?php
          // $pppuser = App\Models\PppUser::where('manager_id', $data->id)->get()->merge(App\Models\Customer::where('manager_id', $data->id)->get())->unique();
          // $mergedData = $table2Data->merge($table1Data)->keyBy('id')->values();
          $pppuser = App\Models\Customer::where('manager_id', $data->id)->get();
          ?>
          @if($data->type == FRANCHISE_MANAGER)
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="franchise-users-tab" data-bs-toggle="tab" data-bs-target="#franchise-users-tab-pane" type="button" role="tab" aria-controls="franchise-users-tab-pane" aria-selected="false">Customers ({{count($pppuser)}})</button>
          </li>
          @endif
        </ul>
        <div class="tab-content p-1" id="myTabContent">
          <div class="tab-pane fade {{$errors->any() ? '':'show active'}}" id="invoice-tab-pane" role="tabpanel" aria-labelledby="invoice-tab" tabindex="0">
            <div class="table-responsive mb-3">
              <table class="table datatable-invoice">
                <thead>
                  <tr>
                    <th>invoice no</th>
                    <th>amount</th>
                    <th>Received amount</th>
                    <th>status</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($data->manager_invoices as $item)
                  <tr>
                    <td>{{$item->invoice_no}}</td>
                    <td>{{$item->amount}}</td>
                    <td>{{$item->received_amount}}</td>
                    <td>{{$item->status}}</td>
                    <td>{{$item->created_at}}</td>
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                        <div class="dropdown-menu">
                          @can('Invoice Details')
                          <a href="{{route('invoice.show', $item->id)}}" class="dropdown-item">View Invoice</a>
                          @endcan
                        </div>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

          </div>
          <div class="tab-pane fade" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              @if($data->type == APP_MANAGER)
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="recive-tab" data-bs-toggle="tab" data-bs-target="#recive-tab-pane" type="button" role="tab" aria-controls="recive-tab-pane" aria-selected="true">Received Balance</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="send-tab" data-bs-toggle="tab" data-bs-target="#send-tab-pane" type="button" role="tab" aria-controls="send-tab-pane" aria-selected="false">Send Balance</button>
              </li>
              @endif
              @if($data->type == FRANCHISE_MANAGER)
              <li class="nav-item" role="presentation">
                <button class="nav-link {{$data->type  == FRANCHISE_MANAGER ? 'active' : '' }} " id="panel-balance-tab" data-bs-toggle="tab" data-bs-target="#panel-balance-tab-pane" type="button" role="tab" aria-controls="panel-balance-tab-pane" aria-selected="false">User Recharge Histories</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="panel-balance-expan-recharge-tab" data-bs-toggle="tab" data-bs-target="#panel-balance-expan-recharge-tab-pane" type="button" role="tab" aria-controls="panel-balance-expan-recharge-tab-pane" aria-selected="false">Panel balance Rechrage</button>
              </li>
              @endif
            </ul>
            <div class="tab-content p-0" id="myTabContent">
              @if($data->type == APP_MANAGER)
              <div class="tab-pane fade show active" id="recive-tab-pane" role="tabpanel" aria-labelledby="recive-tab" tabindex="0">
                <!-- /Recived table -->
                <div class=" my-3">
                  <h5 class="">Received Balance</h5>
                  <div class="table-responsive mb-3">
                    <table class="table datatable-invoice border-top">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Seder Name</th>
                          <th>amount</th>
                          <th>Received amount</th>
                          <th>status</th>
                          <th>Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($data->balanceReciveHistory as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->sender->name}}</td>
                          <td>{{$item->amount}}</td>
                          <td>{{$item->recived_amount}}</td>
                          <td>{{$item->status}}</td>
                          <td>{{$item->created_at}}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="send-tab-pane" role="tabpanel" aria-labelledby="send-tab" tabindex="0">
                <!-- Send table -->
                <div class="my-3">
                  <h5 class="">Transfer Balance</h5>
                  <div class="table-responsive mb-3">
                    <table class="table datatable-invoice border-top">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Reciver Name</th>
                          <th>amount</th>
                          <th>Received amount</th>
                          <th>status</th>
                          <th>Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($data->balanceSendHistory as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->receiver->name}}</td>
                          <td>{{$item->amount}}</td>
                          <td>{{$item->recived_amount}}</td>
                          <td>{{$item->status}}</td>
                          <td>{{$item->created_at}}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              @endif
              <!-- panel-balance -->
              <div class="tab-pane fade {{$data->type  == FRANCHISE_MANAGER ? 'show active' : '' }}" id="panel-balance-tab-pane" role="tabpanel" aria-labelledby="panel-balance-tab" tabindex="0">
                <div class=" my-3">
                  <div class="table-responsive mb-3">
                    <table class="table datatable-invoice border-top">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Amount</th>
                          <th>For</th>
                          <th>Sign</th>
                          <th>status</th>
                          <th>Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $managerBalanceHistory =  App\Models\ManagerBalanceHistory::where(['manager_id' => $data->id, 'sign' => '-'])->latest()->paginate(5);
                        ?>
                        @foreach($managerBalanceHistory as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->balance}}</td>
                          <td>{{$item->history_for}}</td>
                          <td><span class="badge text-bg-{{$item->sign == '+' ? 'success' : 'warning'}}">{{$item->sign}}</span></td>
                          <td>{{$item->status}}</td>
                          <td><span class="badge text-secondary">{{$item->created_at}}</span></td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                    <div class="ml-4 data_table_pagination">{{ $managerBalanceHistory->links() }}</div>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="panel-balance-expan-recharge-tab-pane" role="tabpanel" aria-labelledby="panel-balance-expan-recharge-tab" tabindex="0">
                <div class=" my-3">
                  <div class="table-responsive mb-3">
                    <table class="table datatable-invoice border-top">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Amount</th>
                          <th>For</th>
                          <th>Sign</th>
                          <th>status</th>
                          <th>Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php

                        $managerBalanceHistory =  App\Models\ManagerBalanceHistory::where(['manager_id' => $data->id,  'sign' => '+'])->latest()->paginate(5);
                        ?>
                        @foreach($managerBalanceHistory as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->balance}}</td>
                          <td>{{$item->history_for}}</td>
                          <td><span class="badge text-bg-{{$item->sign == '+' ? 'success' : 'warning'}}">{{$item->sign}}</span></td>
                          <td>{{$item->status}}</td>
                          <td><span class="badge text-secondary">{{$item->created_at}}</span></td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                    <div class="ml-4 data_table_pagination">{{ $managerBalanceHistory->links() }}</div>
                  </div>
                </div>
              </div>
              <!-- panel-balance end-->
            </div>
          </div>
          <div class="tab-pane fade {{$errors->any() ? 'show active' : ''}}" id="password-tab-pane" role="tabpanel" aria-labelledby="password-tab" tabindex="0">
            <form action="{{route('managerChangePassword', $data->id)}}" method="post" class="mt-2">
              @method('put')
              @csrf
              <h5 class="mt-2">Change Password</h5>
              <div class="form-group">
                <label for="password">New Password</label>
                @if($errors->has('password')) <br><span class="text-danger"> {{$errors->first('password')}}</span> @endif
                <input type="text" name="password" id="password" class="form-control">
              </div>
              <div class="form-group">
                <label for="c_password">Confirm Password</label>
                @if($errors->has('password_confirmation'))<br><span class="text-danger"> {{$errors->first('password_confirmation')}}</span> @endif
                <input type="text" name="password_confirmation" id="c_password" class="form-control">
              </div>
              <input type="submit" value="Update Password" class="btn btn-sm btn-outline-primary mt-2">
            </form>
          </div>
          <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
            <h5 class="mt-2">Packages</h5>
            <p class="alert alert-warning text-capitalize"> Custom Sale Price cannot be edited after adding.</p>
            @foreach($data->assignPackage as $index=>$pkg)
            <li class="d-flex mb-2">
              <span class="w-50  border-end">{{$index+1}}. {{ $pkg->package->name }}{{ $pkg->package->synonym ? ' | '.$pkg->package->synonym : '' }}</span>
              <div class="w-50 p-1">
                @if($pkg->is_manager_can_add_custom_package_price)
                @if($pkg->manager_custom_price !== null)
                <span>Custom Sales Price: <strong class="badge bg-label-success">{{$pkg->manager_custom_price}} TK</strong></span>
                @else
                <form class="d-flex" action="{{route('franchise_add_custom_pkg_price', $pkg->id)}}" method="post">
                  @method('put')
                  @csrf
                  <input type="number" name="price" id="" class="form-control" placeholder="Enter your Custom Package Price">
                  <button onclick="return confirm('Are you sure to Edit it')" type="submit" class="btn btn-xs btn-outline-primary"> Save </button>
                </form>
                @endif
              </div>
            </li>
            @endif
            @endforeach
            </li>
          </div>
          <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
            <h5 class="mt-2">Profile</h5>
            <form action="{{route('update_profile', $data->id)}}" enctype="multipart/form-data" method="post">
              @method('put')
              @csrf
              <div class="form-group my-3">
                <input type="hidden" name="profile_for" value="manager">
                <input type="hidden" name="profile_old" value="{{$data->profile_photo_url}}">
                <input type="file" name="profile" id="profile" class="form-control">
              </div>
              <input type="submit" value="Update" class="btn btn-sm btn-primary mt-2">
            </form>
          </div>
          <!-- // franchise users -->
          @if($data->type == FRANCHISE_MANAGER)
          <div class="tab-pane fade" id="franchise-users-tab-pane" role="tabpanel" aria-labelledby="franchise-users-tab" tabindex="0">
            <a href="" id="customer_desabled_btn" class="btn btn-xs btn-danger d-none">Disabled Customers</a>
            <a href="" id="customer_enabled_btn" class="btn btn-xs btn-success d-none">Enabled Customers</a>
            <div class="table-responsive mb-3">
              <table class="table datatable-invoice">
                <thead>
                  <tr>
                    <th>
                      <span class="d-flex">
                        ID
                        <input id="check-all" type="checkbox" class=" ml-4 form-check-input">
                      </span>
                    </th>
                    <th>Mikrotik Disabled</th>
                    <th>user Name</th>
                    <th>Bill</th>
                    <th>discount</th>
                    <th>Package</th>
                    <th>Expire Date</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($pppuser as $item)
                  <tr>
                    <td>
                      <div class="d-flex">
                        {{$item->id}}
                        @if($item->username)
                        <input type="checkbox" name="customers_id[]" class="check-item ml-4 form-check-input" value="{{$item->id}}">
                        @endif
                      </div>
                    </td>
                    <td>{{(isset($item->mikrotik_disabled) ? ($item->mikrotik_disabled == 0 ? 'Yes' :'No') : 'N/A')}}</td>
                    <td>{{$item->username}}</td>
                    <td>{{$item->bill ? $item->bill : 'N/A'}}</td>
                    <td>{{$item->discount ? $item->discount : 'N/A'}}</td>
                    <td>{{$item->package ? $item->package->name : $item->profile}}</td>
                    <td>{{$item->expire_date ? $item->expire_date : 'N/A'}}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  <!--/ User Content -->

</div>
@endsection

@push('pricing-script')
<script script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
  // jQuery code for handling the checkbox functionality
  $(document).ready(function() {

    //suspended user
    $('#customer_desabled_btn').click(function() {
      if (confirm('Are you sure to desabled selected user')) {
        var checkedItems = $("input:checkbox[name='customers_id[]']:checked");
        var checkedValues = [];

        checkedItems.each(function() {
          checkedValues.push($(this).val());
        });
        axios.post(`/disabled-multiple-customer`, {
          'customers_id': checkedValues,
          'status': 1
        }).then((resp) => {
          window.location.reload();
        });
      }
    });
    //suspended user
    $('#customer_enabled_btn').click(function() {
      if (confirm('Are you sure to enabled selected user')) {
        var checkedItems = $("input:checkbox[name='customers_id[]']:checked");
        var checkedValues = [];

        checkedItems.each(function() {
          checkedValues.push($(this).val());
        });
        axios.post(`/disabled-multiple-customer`, {
          'customers_id': checkedValues,
          'status': 0
        }).then((resp) => {
          window.location.reload();
        });
      }
    });

    //check and uncheck btn 
    $('#check-all').change(function() {
      const isChecked = $(this).prop('checked');
      $('#customer_desabled_btn').toggleClass('d-none', !isChecked);
      $('#customer_enabled_btn').toggleClass('d-none', !isChecked);
      $('.check-item').prop('checked', isChecked);
    });

    // Check/uncheck the items within a group
    $('.check-item').change(function() {
      // Uncheck "All" if any item is unchecked
      const allChecked = $('.check-item:not(:checked)').length === 0;
      $('#check-all').prop('checked', allChecked);

      // Show/Hide the suspend button based on the checked items
      const anyChecked = $('.check-item:checked').length > 0;
      $('#customer_desabled_btn').toggleClass('d-none', !anyChecked);
      $('#customer_enabled_btn').toggleClass('d-none', !anyChecked);
    });
  });
</script>
@endpush