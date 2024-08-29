@extends('layouts/layoutMaster')
@section('title')
    {{ 'Sub zone' }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-3">Sub zone</h5>
            @can('Sub-Zone Add')
                <a href='{{ route('network-add-sub-zone') }}' class="btn btn-primary">Add item</a>
            @endcan
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                    <tr>
                        <th>SL No.</th>
                        <th>Name</th>
                        <th>Zone</th>
                        <th>Total User</th>
                        <th>Total Active</th>
                        <th>Total Inactive</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sub_zones as $sub_zone)
                        <tr>
                            <td>{{ $sub_zone->id }}</td>
                            <td>{{ $sub_zone->name }}</td>
                            <td>{{ $sub_zone->zone->name }}</td>
                            <td>{{ count($sub_zone->customer) }}</td>
                            <td>{{ count($sub_zone->customer->where('status', CUSTOMER_ACTIVE)) }}</td>
                            <td>{{ count($sub_zone->customer->where('status', '!=', CUSTOMER_ACTIVE)) }}</td>
                            <td>
                                @can('Sub-Zone Edit')
                                    <a class="btn btn-sm btn-primary" href="{{ route('network-edit-sub-zone', $sub_zone->id) }}">
                                        <div class="cursor-pointer">
                                            <i class="bi bi-pencil-square"></i>
                                        </div>
                                    </a>
                                @endcan
                                @can('Sub-Zone Delete')
                                  @php $url = "network-delete-sub-zone/$sub_zone->id" @endphp
                                    <button class="btn btn-sm btn-danger"
                                        onclick='openConfirmation("{{ url($url) }}","get")'> <i class="bi bi-trash"></i></button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="ml-4 data_table_pagination">{{ $sub_zones->links() }}</div>
        </div>
    </div>
@endsection
