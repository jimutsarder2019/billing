@extends('layouts/layoutMaster')
@section('title', 'mini-dashboard')
@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Mini Dashboard @if (request('username'))
                    ({{ request('username') }}) @endif
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-4 mb-4">
                <div class="col-sm-12 col-md-4">
                    <form action="{{ route('mini-dashboard') }}" method="get" class="">
                        <div class="row">
                            <div class="col-9">
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
                            <div class="col-3">
                                <!-- <input type="text" class="form-control" placeholder="Search username" value="{{ request('username') }}" name="username" aria-label="Recipient's username" aria-describedby="button-addon2"> -->
                                <div class="d-flex">
                                    <!-- <button class="btn btn-outline-primary" type="submit" id="button-addon2">Search</button> -->
                                    <a href="{{ route('mini-dashboard') }}" class="btn btn-outline-warning">Clear</a>
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
    @endsection
