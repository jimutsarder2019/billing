@extends('layouts/layoutMaster')
@section('title')
    {{ 'onu' }}
@endsection
@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-3">View ONU</h5>
            @can('ONU Add')
                <a href='{{ route('onu.create') }}' class="ml-4 btn btn-primary">Add item</a>
            @endcan
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table">
                <thead>
                    <tr>
                        <th>SL No.</th>
                        <th>name</th>
                        <th>mac</th>
                        <th>olt id</th>
                        <th>pon port</th>
                        <th>onu id</th>
                        <th>rx power</th>
                        <th>distance</th>
                        <th>username</th>
                        <th>vlan tagged</th>
                        <th>vlan id</th>
                        <th>zone id</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->mac }}</td>
                            <td>{{ $item->olt_id }}</td>
                            <td>{{ $item->pon_port }}</td>
                            <td>{{ $item->onu_id }}</td>
                            <td>{{ $item->rx_power }}</td>
                            <td>{{ $item->distance }}</td>
                            <td>{{ $item->customer->username }}</td>
                            <td>{{ $item->vlan_tagged == 1 ? 'Yes' : 'No' }}</td>
                            <td>{{ $item->vlan_id }}</td>
                            <td>{{ $item->zone->name }}</td>
                            <td>
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i
                                        class="ti ti-dots-vertical"></i></button>
                                <div class="dropdown-menu text-start">
                                    @can('ONU Edit')
                                        <a class="dropdown-item text-primary" href="{{ route('onu.edit', $item->id) }}">
                                            <div class="cursor-pointer">
                                                <i class="bi bi-pencil-square"></i> edit
                                            </div>
                                        </a>
                                    @endcan
                                    @can('ONU Delete')
                                        @php $url = "network/onu/$item->id" @endphp
                                        <button type="submit" class="dropdown-item text-danger"
                                            onclick='openConfirmation("{{ url($url) }}")'><i class="bi bi-trash"></i>
                                            Delete</button>
                                    @endcan
                                </div>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
