@extends('layouts/layoutMaster')
@section('title')
    {{ $data->full_name }}
@endsection
@section('page-style')
    <style>
        .dataTables_info,
        .dataTables_paginate {
            display: block !important;
        }

        th {
            font-weight: 300 !important;
        }

        .bg-lighten {
            background-color: #f3f7f9;
        }

        .w-40 {
            width: 40% !important;
        }

        small {
            font-size: 0.9125rem !important;
        }
    </style>
@endsection
@section('content')
    <div class="card mb-3 px-2 py-2">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">User / View / {{ $data->full_name }}</span>
        </h4>
    </div>
    <div class="row">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <!-- User Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="user-avatar-section">
                        <div class=" d-flex align-items-center flex-column">
                            <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                src='{{ $data->avater ? asset($data->avater) : asset(MANAGER_DEFAULT_LOG) }}' height="100"
                                width="100" alt="{{ $data->profile_photo_url }}" />
                            <div class="user-info text-center">
                                <h4 class="mb-2">{{ $data->full_name }}</h4>
                                <span class="badge bg-label-secondary mt-1">Customer</span>
                                <br>
                                <p class="pt-2"> Bill: {{ $data->bill }} ৳</p>
                                @if ($data->allow_grace)
                                    <span class="badge bg-label-warning mt-1">Grace : {{ $data->allow_grace }} Days</span>
                                @endif
                                <span>{{ $data->phone }}</span>
                                <div class="mt-2">
                                    Added By :
                                    <span class="badge bg-label-secondary mt-1">{{ $data->manager->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-container pt-4">
                        <div class="d-flex justify-content-center">
                            @if (isset($data->mikrotik))
                                <a href="{{ route('mikrotik_info', ['username' => $data->username, 'id' => $data->mikrotik]) }}"
                                    class="btn btn-sm btn-primary">Live Graph</a>
                            @endif
                            <a href="{{ route('user-edit-customer', $data->id) }}"
                                class="btn btn-sm btn-primary mx-3">Edit</a>
                            <a title="Click here to {{ $data->mikrotik_disabled == STATUS_TRUE ? 'Enable' : 'Disable' }} this user"
                                onclick=' return confirm("Are you sure to {{ $data->mikrotik_disabled == STATUS_TRUE ? 'Enable' : 'Disable' }} this user")'
                                href="{{ route('disable-customer', ['id' => $data->id, 'status' => $data->mikrotik_disabled == STATUS_TRUE ? STATUS_FALSE : STATUS_TRUE]) }}"
                                class="btn btn-sm btn-label-{{ $data->mikrotik_disabled == STATUS_TRUE ? 'warning' : 'success' }}">{{ $data->mikrotik_disabled == STATUS_TRUE ? 'Disable' : 'Enable' }}</a>
                        </div>
                        @can('Delete Users')
                            <div title="Please Disable the user first" class="mt-3">
                                <a style="pointer-events: {{ $data->mikrotik_disabled == STATUS_TRUE ? '' : 'none' }}"
                                    onclick="return confirm('Are you sure to Deete This user')"
                                    href="{{ route('customer-suspended', $data->id) }}"
                                    class="btn btn-sm btn-label-danger suspend-user w-100 mt-3">Delete</a>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <!--/ User Sidebar -->
        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <div class="row mb-3">
                <div class="col-sm-12 col-md-6">
                    <div class="card p-2">
                        <small class="text-center mb-2 font-semibold">Customer & Billing Information</small>
                        <table class="border">
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Full Name</small></th>
                                <th class="border ps-2"><small>{{ $data->full_name }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Balance</small></th>
                                <th class="border ps-2"><small>{{ $data->wallet ?? 00 }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Package</small></th>
                                <th class="border ps-2"><small>{{ $data->package->name }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Bill</small></th>
                                <th class="d-flex ps-2">
                                    <small class="w-50 pe-1">{{ $data->bill }} ৳</small>
                                    <small class="w-50 border-start ps-1">Discount: {{ $data->discount ?? 00 }} ৳</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Expire Date</small></th>
                                <th class="border ps-2 text-success">
                                    <small>{{ \Carbon\Carbon::parse($data->expire_date)->format('Y-m-d h:i:s A') }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Grace</small></th>
                                <th class="border ps-2"><small>{{ $data->allow_grace ?? 0 }} Days</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Billing Status:</small></th>
                                <th class="border ps-2"><small class="text-capitalize">{{ $data->status }}</small></th>
                            </tr>


                        </table>
                    </div>
                    <div class="card p-2 mt-3">
                        <small class="text-center mb-2 font-semibold">Server Information</small>
                        <table class="border">
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Server</small></th>
                                <th class="border ps-2"><small>{{ $data->service }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Username</small></th>
                                <th class="border ps-2"><small>{{ $data->username }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Password</small></th>
                                <th class="border ps-2"><small>{{ $data->password }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Mikrotik:</small></th>
                                <th class="border ps-2">
                                    <small>{{ isset($data->mikrotik) ? $data->mikrotik->identity : '' }} |
                                        {{ $data->mikrotik ? $data->mikrotik->host : '' }} </small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Active Mac:</small></th>
                                <th class="border ps-2">
                                    <small> {{ $mkt_data['caller-id'] ?? '' }}<i class="fa fa-server"></i></small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Uptime:</small></th>
                                <th class="border ps-2">
                                    <small>{{ $mkt_data['uptime'] ?? '' }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Last Logout:</small></th>
                                <th class="border ps-2"><small>
                                        {{ $mkt_data['last-logged-out'] ?? '' }}
                                    </small>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card p-2 mt-1">
                        <small class="text-center mb-2 font-semibold">Basic Information</small>
                        <table class="border">
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Father Name</small></th>
                                <th class="border ps-2"><small>{{ $data->father_name ?? 'N\A' }}</small></th>
                            </tr>

                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Mother Name</small></th>
                                <th class="border ps-2"><small>{{ $data->mother_name ?? 'N\A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Address</small></th>
                                <th class="border ps-2"><small>{{ $data->address ?? 'N\A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Phone</small></th>
                                <th class="border ps-2"><small>{{ $data->phone }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Email</small></th>
                                <th class="border ps-2"><small>{{ $data->Email ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Gender</small></th>
                                <th class="border ps-2"><small>{{ $data->gender ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>NID</small></th>
                                <th class="border ps-2"><small>{{ $data->national_id ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Date Of Birth</small></th>
                                <th class="border ps-2"><small>{{ $data->date_of_birth ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Zone</small></th>
                                <th class="border ps-2"><small>{{ $data->zone->name ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Sub Zone</small></th>
                                <th class="border ps-2"><small>{{ $data->sub_zone->name ?? 'N/A' }}</small></th>
                            </tr>

                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Purchase Package</small></th>
                                <th class="border ps-2"><small>{{ $data->purchase_package->name ?? 'N/A' }}</small></th>
                            </tr>

                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Send Sms</small></th>
                                <th class="border ps-2"><small>{{ $data->is_send_sms == 1 ? 'Yes' : 'NO' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Registration Date</small></th>
                                <th class="border ps-2">
                                    <small>{{ \Carbon\Carbon::parse($data->registration_date)->format('Y-m-d h:i:s A') }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Connection Date</small></th>
                                <th class="border ps-2">
                                    <small>{{ \Carbon\Carbon::parse($data->connection_date)->format('Y-m-d h:i:s A') }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Manager</small></th>
                                <th class="border ps-2"><small>{{ $data->manager->name }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Disabled In Mikrotik</small></th>
                                <th class="border ps-2">
                                    <small>{{ $data->mikrotik_disabled == 0 ? 'False' : 'True' }}</small>
                                </th>
                            </tr>


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link  {{ !$errors->any() ? 'active' : '' }}" id="invoice-tab" data-bs-toggle="tab"
                data-bs-target="#invoice-tab-pane" type="button" role="tab" aria-controls="invoice-tab-pane"
                aria-selected="true">Invoices</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="Package-change-history-tab" data-bs-toggle="tab"
                data-bs-target="#Package-change-history-tab-pane" type="button" role="tab"
                aria-controls="Package-change-history-tab-pane" aria-selected="false">Package History</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $errors->any() ? 'active' : '' }}" id="change-password-tab" data-bs-toggle="tab"
                data-bs-target="#change-password-tab-pane" type="button" role="tab"
                aria-controls="change-password-tab-pane" aria-selected="false">Change Password</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-capitalize" id="profile-tab" data-bs-toggle="tab"
                data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane"
                aria-selected="false">Profile</button>
        </li>
        @if (auth()->user()->type == 'app_manager')
            <li class="nav-item" role="presentation">
                <button class="nav-link text-capitalize" id="grace-tab" data-bs-toggle="tab"
                    data-bs-target="#grace-tab-pane" type="button" role="tab" aria-controls="grace-tab-pane"
                    aria-selected="false">Grace & ticket</button>
            </li>
        @endif

        <li class="nav-item" role="presentation">
            <button class="nav-link text-capitalize" id="note-tab" data-bs-toggle="tab" data-bs-target="#note-tab-pane"
                type="button" role="tab" aria-controls="note-tab-pane" aria-selected="false">Notes</button>
        </li>
    </ul>
    <div class="tab-content p-1" id="myTabContent">
        <div class="tab-pane fade {{ $errors->any() ? '' : 'show active' }}" id="invoice-tab-pane" role="tabpanel"
            aria-labelledby="invoice-tab" tabindex="0">
            <!-- Invoice table -->
            <div class="mb-4">
                <h5 class="card-header">Invoice</h5>
                <div class="card-datatable table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th><i class="fa fa-cogs"></i></th>
                                <th>ID</th>
                                <th>invoice NO</th>
                                <th>amount</th>
                                <th>invoice_for</th>
                                <th>Issued Date</th>
                                <th>received amount</th>
                                <th>status</th>
                                <th>received Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                            use App\Models\CustomerEditHistory;
                            
                            $invoices = App\Models\Invoice::where('customer_id', $data->id)
                                ->latest()
                                ->paginate(5);
                            ?>
                            @foreach ($invoices as $inv_item)
                                <tr>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                            <div class="dropdown-menu">
                                                @can('Invoice Details')
                                                    <a href="{{ route('invoice.show', $inv_item->id) }}"
                                                        class="dropdown-item text-primary">View Invoice</a>
                                                @endcan
                                                @can('Refund Payments')
                                                    @if ($inv_item->id == $invoices->first()->id && $inv_item->status == STATUS_PAID)
                                                        <div title="Refund Invoice" data-bs-toggle="modal"
                                                            data-bs-target="#refund_{{ $inv_item->id }}">
                                                            <div class="dropdown-item cursor-pointer text-success">
                                                                <i class="bi bi-arrow-counterclockwise"></i> Refund
                                                            </div>
                                                        </div>
                                                        <!-- elseif($inv_item->status !== STATUS_PAID)
                                                                                                                        @can('Invoice Payments')
                                                                                                                                                            <a href="{{ route('invoice_payment_get', $inv_item->id) }}" class="dropdown-item cursor-pointer text-success">Payment</a>
                                                                                                                                                            @endif -->
                                                        @endif
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                        @include('content/customer/modal-invoice-refund', [
                                            'id' => $inv_item->id,
                                        ])

                                        <td>{{ $inv_item->id }}</td>
                                        <td>{{ $inv_item->invoice_no }}</td>
                                        <td>{{ $inv_item->amount }}</td>
                                        <td>{{ $inv_item->invoice_for }}</td>
                                        <td>{{ $inv_item->created_at->format('Y-d-m h:m A') }}</td>
                                        <td>{{ $inv_item->received_amount }}</td>
                                        <td>{{ $inv_item->status }}</td>
                                        <td>
                                            @if ($inv_item->status !== STATUS_PENDING)
                                                {{ $inv_item->updated_at->format('d-m-Y h:i:s a') }} @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                        <div class="ml-4 data_table_pagination">{{ $invoices->links() }}</div>
                    </div>

                </div>
            </div>
            <div class="tab-pane fade" id="Package-change-history-tab-pane" role="tabpanel"
                aria-labelledby="Package-change-history-tab" tabindex="0">
                <div class=" mb-4">
                    <h5 class="card-header">Package History</h5>
                    <div class="table-responsive mb-3">
                        <table class="table datatable-invoice">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($data->packageHistory->count() > 0)
                                    @foreach ($data->packageHistory as $item)
                                        <tr>
                                            <td>{{ $item->package ? $item->package->id : 'N/A' }}</td>
                                            <td>{{ $item->package ? $item->package->name : 'N/A' }}</td>
                                            <td>{{ $item->package ? $item->package->price : '0' }} TK</td>
                                            <td>{{ $item->created_at }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    @php
                                        $purchese_package = App\Models\Package::where(
                                            'id',
                                            $data->purchase_package_id,
                                        )->first();
                                    @endphp
                                    @if ($purchese_package)
                                        <tr>
                                            <td>{{ $purchese_package->id }}</td>
                                            <td>{{ $purchese_package->name }}</td>
                                            <td>{{ $purchese_package->price }} TK</td>
                                            <td>{{ $purchese_package->created_at }}</td>
                                        </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="balance-history-tab-pane" role="tabpanel" aria-labelledby="balance-history-tab"
                tabindex="0">
                <div class="mb-4">
                    <h5 class="card-header">balance History</h5>
                    <div class="table-responsive mb-3">
                        <table class="table datatable-invoice">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th>Issued Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->invoice as $inv_item)
                                    <tr>
                                        <td>{{ $inv_item->id }}</td>
                                        <td>{{ $inv_item->invoice_no }}</td>
                                        <td>{{ $inv_item->amount }}</td>
                                        <td>{{ $inv_item->received_amount }}</td>
                                        <td>{{ $inv_item->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $errors->any() ? 'show active' : '' }} p-2" id="change-password-tab-pane"
                role="tabpanel" aria-labelledby="change-password-tab" tabindex="0">
                <form action="{{ route('customerChangePassword', $data->id) }}" method="post" class="mt-2">
                    @method('put')
                    @csrf
                    <p>
                    <h5 class="mt-2">Change Password</h5>
                    <div class="form-group">
                        <label for="password">New Password</label>
                        @if ($errors->has('password')) <br><span class="text-danger">
                                {{ $errors->first('password') }}</span> @endif
                        <input type="text" name="password" id="password" value="{{ old('password') }}"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="c_password">Confirm Password</label>
                        @if ($errors->has('password_confirmation'))<br><span
                                class="text-danger">{{ $errors->first('password_confirmation') }}</span> @endif
                        <input type="text" name="password_confirmation" id="c_password"
                            value="{{ old('password_confirmation') }}" class="form-control">
                    </div>
                    <input type="submit" value="Update Password" class="btn btn-sm bg-label-primary mt-2">
                </form>
            </div>
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                <h5 class="mt-2">Profile</h5>
                <form action="{{ route('update_profile', $data->id) }}" enctype="multipart/form-data" method="post">
                    @method('put')
                    @csrf
                    <div class="form-group my-3">
                        <input type="hidden" name="profile_for" value="customer">
                        <input type="hidden" name="profile_old" value="{{ $data->profile_photo_url }}">
                        <input type="file" name="profile" id="profile" class="form-control">
                    </div>
                    <input type="submit" value="Update" class="btn btn-sm bg-label-primary mt-2">
                </form>
            </div>
            @if (auth()->user()->type == 'app_manager')
                <div class="tab-pane fade" id="grace-tab-pane" role="tabpanel" aria-labelledby="grace-tab" tabindex="0">
                    <h5 class="pl-3 mt-2">Grace History</h5>
                    <div class="table-responsive mb-3">
                        <table class="table datatable-invoice">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Actual Expire Date</th>
                                    <th>Grace Allowed (Days)</th>
                                    <th>Expire Date (After Grace)</th>
                                    <th>Manager</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (App\Models\CustomerGraceHistorys::with('manager')->where('customer_id', $data->id)->latest()->get() as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->grace_before_expire_date }}</td>
                                        <td>{{ $item->grace }} Days</td>
                                        <td>{{ $item->customer_new_expire_date }}</td>
                                        <td>{{ $item->manager->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="ps-3 mt-3">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="card-title m-0 me-2 pt-1 mb-2">Tickets</h5>
                            <div class="dropdown">
                                <?php $tickets = App\Models\Ticket::where('customer_id', $data->id)->paginate(10); ?>
                                <div data-bs-toggle="tooltip" data-bs-title="Total Tickets" id="item_total"
                                    class="badge bg-primary">{{ $tickets->total() }}</div>
                                <div data-bs-toggle="tooltip" data-bs-title="Tickets Pending" id="item_pending"
                                    class="badge bg-warning">
                                    {{ App\Models\Ticket::where(['customer_id' => $data->id, 'status' => 'pending'])->count() }}
                                </div>
                                <div data-bs-toggle="tooltip" data-bs-title="Processing Tickets" id="item_processing"
                                    class="badge bg-info">
                                    {{ App\Models\Ticket::where(['customer_id' => $data->id, 'status' => 'processing'])->count() }}
                                </div>
                                <div data-bs-toggle="tooltip" data-bs-title="Total Completed" id="item_success"
                                    class="badge bg-success">
                                    {{ App\Models\Ticket::where(['customer_id' => $data->id, 'status' => 'completed'])->count() }}
                                </div>
                            </div>
                        </div>
                        <div class="card-body pb-0">
                            <div class="table-responsive mb-3">
                                <table class="table datatable-invoice">
                                    <thead>
                                        <tr>
                                            <th>ticket No</th>
                                            <th>Created Date</th>
                                            <th>Solved Date</th>
                                            <th>Status</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tickets as $index => $item)
                                            <tr>
                                                <td>{{ $item->ticket_no }}</td>
                                                <td>{{ $item->created_at->format('d-m-y h:m a') }}</td>
                                                <td>{{ $item->updated_at->format('d-m-y h:m a') }}</td>
                                                <td>{{ $item->status }}</td>
                                                <td><a href='{{ route('ticket.show', $item->id) }}'><i
                                                            class="bi bi-eye"></i></a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="ml-4 data_table_pagination">{{ $tickets->links() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="tab-pane fade" id="note-tab-pane" role="tabpanel" aria-labelledby="note-tab" tabindex="0">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mt-2">Note</h5>
                        <?php
                        $note_histories = CustomerEditHistory::with('manager')
                            ->select('id', 'manager_id', 'subject', 'note', 'created_at')
                            ->where('customer_id', $data->id)
                            ->latest()
                            ->get();
                        ?>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <ul id="note_timeline" class="timeline mb-0">
                                    @foreach ($note_histories as $t_history)
                                        <li id="note_{{ $t_history->id }}" class="timeline-item timeline-item-transparent">
                                            <span class="timeline-point timeline-point-success"></span>
                                            <div id="note_{{ $t_history->id }}" class="timeline-event">
                                                <div class="timeline-header mb-sm-0 mb-3">
                                                    <h6 class="mb-0 text-capitalize">{{ $t_history->subject }} </h6>
                                                    <span
                                                        class="mb-0 text-capitalize bg-label-success badge badge-pill">{{ $t_history->manager->name }}
                                                    </span>
                                                    <div class="d-flex">
                                                        <span
                                                            class="text-muted">{{ $t_history->created_at->format('d-m-y h:i A') }}</span>
                                                        <div class="dropdown ms-2">
                                                            <button type="button"
                                                                class="badge badge-center rounded-pill bg-label-success btn p-3 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown"><i
                                                                    class="ti ti-dots-vertical"></i></button>
                                                            <div class="dropdown-menu">
                                                                <button data_attr="{{ $t_history }}"
                                                                    class="note_edit_btn dropdown-item text-warning">Edit</button>
                                                                <button data_attr="{{ $t_history->id }}"
                                                                    class="note_delete_btn dropdown-item text-danger">Delete</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p>{{ $t_history->note }} </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <label for="note">Note</label>
                                <textarea name="customer_note" id="customer_note" placeholder="Note" class="form-control mb-1" rows="5"></textarea>
                                <input type="hidden" name="customer_id" value="{{ $data->id }}">
                                <input type="hidden" name="note_id" value="">
                                <button id="note_save_btn" type="button" class="btn btn-success">Save</button>
                                <button id="note_reset_btn" type="button" class="btn btn-warning">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ User Content -->
        </div>
    @endsection
    @push('pricing-script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"
            integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                const customer_id = document.querySelector("input[name='customer_id']").value;
                let app_url = document.head.querySelector('meta[name="app_url"]').content;
                //new phone item_btn
                $("#note_save_btn").click(function() {
                    const customer_note = document.querySelector('textarea[name="customer_note"]').value;
                    const note_id = document.querySelector("input[name='note_id']").value;
                    const inputContainer = $("#note_timeline");

                    axios.post(`${app_url}/save-customer-note`, {
                        customer_note: customer_note,
                        customer_id: customer_id,
                        note_id: note_id,
                    }).then((resp) => {
                        const data = resp.data.data;
                        if (resp.data.success) {
                            console.log(data);
                            const created_date = moment(data.created_at).format('DD-MM-YY hh:mm A');
                            // const html = ;

                            if (note_id) {
                                const oldChild = document.getElementById(`note_${note_id}`);
                                if (oldChild) {
                                    oldChild.innerHTML =
                                        `   <div class="timeline-event">
                                        <div class="timeline-header mb-sm-0 mb-3">
                                            <h6 class="mb-0 text-capitalize">${data.subject}</h6>
                                            <div class="d-flex">
                                            <span class="text-muted">${created_date}</span>
                                                <div class="dropdown ms-2">
                                                    <button type="button" class="badge badge-center rounded-pill bg-label-success btn p-3 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                                    <div class="dropdown-menu">
                                                        <button data_attr="${JSON.stringify(data)}" class="note_edit_btn dropdown-item text-warning">Edit</button>
                                                        <button data_attr="${data.id}" class="note_delete_btn dropdown-item text-danger">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p>${data.note}</p>
                                    </div>`
                                }
                            } else {
                                inputContainer.prepend(`
                            <li id="note_${data.id}" class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-success"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-sm-0 mb-3">
                                        <h6 class="mb-0 text-capitalize">${data.subject}</h6>
                                        <div class="d-flex">
                                            <span class="text-muted">${created_date}</span>
                                            <div class="dropdown ms-2">
                                                    <button type="button" class="badge badge-center rounded-pill bg-label-success btn p-3 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                                    <div class="dropdown-menu">
                                                        <button data_attr="${JSON.stringify(data)}" class="note_edit_btn dropdown-item text-warning">Edit</button>
                                                        <button data_attr="${data.id}" class="note_delete_btn dropdown-item text-danger">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <p>${data.note}</p>
                                </div>
                            </li>
                        `);
                            }
                            // Clear input fields after updating or adding the element
                            document.querySelector('textarea[name="customer_note"]').value = '';
                            document.querySelector("input[name='note_id']").value = '';
                            $('#note_save_btn').text('Save');
                        }
                    }).catch(e => {
                        console.error('Error:', e);
                    });
                });





                // click note_edit_btn
                $('.note_edit_btn').click(function() {
                    console.log('note_edit_btn');
                    const data = JSON.parse($(this).attr("data_attr"))
                    console.log(data);
                    document.querySelector('textarea[name="customer_note"]').value = data.note
                    document.querySelector("input[name='note_id']").value = data.id
                    $('#note_save_btn').text('Update')
                })
                // note_reset_btn
                $('#note_reset_btn').click(function() {
                    document.querySelector('textarea[name="customer_note"]').value = ''
                    document.querySelector("input[name='note_id']").value = ''
                    $('#note_save_btn').text('Save')
                })

                //note_delete_btn
                $('.note_delete_btn').click(function() {
                    const data = JSON.parse($(this).attr("data_attr"))
                    console.log(data);
                    url = `${app_url}/delete-customer-note/${data}`
                    openConfirmation(url);
                })

                // Event delegation for delete button
                $("#input-container").on("click", ".remove-item-btn", function() {
                    $(this).closest('.input-item').remove();
                });
            });
        </script>
    @endpush
