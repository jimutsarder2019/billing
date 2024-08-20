@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('vendor-style')
    <!-- Vendor -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
@endsection

@section('page-style')
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endsection

@section('content')
    <div class="container-xxl">
        <div class="container-p-y">
            <div class="authentication-inner py-4">
                <table class="table-strip">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>invoice_no</th>
                            <th>Name</th>
                            <th>package</th>
                            <th>amount</th>
                            <th>Payment Date</th>
                            <th>User New Expire date</th>
                            <th>status</th>
                            <th>received amount</th>
                            <th>Manager</th>
                            <th>Created date</th>
                            <th>Invoice For</th>
                            <th>Method</th>
                            <th>due amount</th>
                            <th>advanced amount</th>
                            <th>Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td><a class="text-{{ $item->status == STATUS_PAID ? 'success' : ($item->status == STATUS_DUE ? 'danger' : ($item->status == STATUS_OVER_PAID ? 'success' : ($item->status == STATUS_PENDING ? 'warning' : 'danger'))) }}"
                                        href="{{ route('invoice.show', $item->id) }}">{{ $item->invoice_no }}</a></td>
                                @if ($item->customer)
                                    <td><a
                                            href="{{ route('customer-user.show', $item->customer->id) }}">{{ $item->customer->username }}</a>
                                    </td>
                                @else
                                    @if ($item->manager)
                                        @if ($item->invoice_for == INVOICE_MANAGER_ADD_PANEL_BALANCE && $item->franchise_manager)
                                            <td><a
                                                    href="{{ route('managerProfile', $item->franchise_manager->id) }}">{{ $item->franchise_manager->name }}</a>
                                            </td>
                                        @else
                                            <td><a
                                                    href="{{ route('managerProfile', $item->manager->id) }}">{{ $item->manager->name }}</a>
                                            </td>
                                        @endif
                                    @endif
                                @endif
                                <td>{{ $item->package ? $item->package->name : '' }}</td>
                                <td>{{ $item->amount }}</td>
                                <td>
                                    @if ($item->status !== STATUS_PENDING)
                                        {{ $item->updated_at->format('d-m-Y h:i:s a') }}
                                    @endif
                                </td>
                                <td>{{ $item->customer_new_expire_date ? \Carbon\Carbon::parse($item->customer_new_expire_date)->format('Y-m-d h:i:s A') : '' }}
                                </td>
                                <td>
                                    <span
                                        class="text-capitalize badge bg-label-{{ $item->status == STATUS_PAID
                                            ? 'success'
                                            : ($item->status == STATUS_DUE
                                                ? 'danger'
                                                : ($item->status == STATUS_PENDING
                                                    ? 'warning'
                                                    : 'secondary')) }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td>{{ $item->received_amount }}</td>
                                <td>{{ $item->manager ? $item->manager->name : 'N/A' }}</td>
                                <td>{{ $item->created_at->format('Y-m-d h:i:s A') }}</td>
                                <td> <span class="text-capitalize">{{ str_replace('_', ' ', $item->invoice_for) }}</span>
                                </td>
                                <td>{{ $item->paid_by }}</td>
                                <td>{{ $item->due_amount }}</td>
                                <td>{{ $item->advanced_amount }}</td>
                                <td>{{ $item->comment }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
@endsection
