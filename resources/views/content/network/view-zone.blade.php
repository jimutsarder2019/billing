@extends('layouts/layoutMaster')
@section('title')
    {{ 'zone' }}
@endsection
@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-3">Zone</h5>
            @can('Zone Add')
                <a href='{{ route('network-add-zone') }}' class="btn btn-primary">Add item</a>
            @endcan
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                    <tr>
                        <th>SL No.</th>
                        <th>Zone Name</th>
                        <th>Zone Abbreviation</th>
                        <th>Status</th>
                        <th>Total Sub-Zone</th>
                        <th>Total User</th>
                        <th>Total Active</th>
                        <th>Total Inactive</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($zones as $zone)
                        <tr>
                            <td>{{ $zone->id }}</td>
                            <td>{{ $zone->name }}</td>
                            <td>{{ $zone->abbreviation }}</td>
                            <td>
                                {{ $zone->status == 1 ? 'Active' : 'In Active' }}
                            </td>
                            <td>{{ count($zone->sub_zone) }}</td>
                            <td>{{ count($zone->customer) }}</td>
                            <td>{{ count($zone->customer->where('status', CUSTOMER_ACTIVE)) }}</td>
                            <td>{{ count($zone->customer->where('status', '!=', CUSTOMER_ACTIVE)) }}</td>
                            <td>
                                @can('Zone Edit')
                                    <a class="btn btn-sm btn-primary" href="{{ route('network-edit-zone', $zone->id) }}">
                                        <div class="cursor-pointer">
                                            <i class="bi bi-pencil-square"></i>
                                        </div>
                                    </a>
                                @endcan
                                @can('Zone Delete')
                                    @php $url = "network/zone/$zone->id" @endphp
                                    <button class="btn btn-sm btn-danger"
                                        onclick='openConfirmation("{{ url($url) }}","get")'>
                                        <div class="cursor-pointer">
                                            <i class="bi bi-trash"></i>
                                        </div>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="ml-4 data_table_pagination">{{ $zones->links() }}</div>

        </div>
    </div>
@endsection
