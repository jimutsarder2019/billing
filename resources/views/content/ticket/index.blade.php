@extends('layouts/layoutMaster')
@php $route = 'ticket' @endphp
@section('title', 'ticket')
@section('content')
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Tickets</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $data->total() }}</h4>
                                <!-- <span class="text-success">(+29%)</span> -->
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-users"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Pending </span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $pending }}</h4>
                                <!-- <span class="text-success">(+18%)</span> -->
                            </div>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="ti ti-user-plus ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Processing</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $processing }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-user-check ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Completed</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $completed }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-user-check ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card pb-3">
        <div class="card-header">
            <h3>Tickets
            </h3>
            <div class="d-flex">
                <form action='{{ route("$route.index") }}'>
                    <select class="form-control" name="item" onchange="this.form.submit()" id="">
                        <option @if ($data->count() == '10') selected @endif value="10">10</option>
                        <option @if ($data->count() == '50') selected @endif value="50">50</option>
                        <option @if ($data->count() == '100') selected @endif value="100">100</option>
                        <option @if ($data->count() == $data->total()) selected @endif value="{{ $data->total() }}">All</option>
                    </select>
                </form>
                @can('Ticket Add')
                    <a href='{{ route("$route.create") }}' class="ml-4 btn btn-primary">Add item</a>
                @endcan
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th><i class="fa fa-cogs"></i></th>
                        <th>ticket No</th>
                        <th>Name</th>
                        <th>phone</th>
                        <th>Created Date</th>
                        <th>Priority</th>
                        <th>Category</th>
                        <th>status</th>
                        <th>Solved At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                    <div class="dropdown-menu text-center">
                                        @can('Ticket Change Status')
                                            <a class="dropdown-item  text-primary"
                                                href='{{ route("$route.show", $item->id) }}'><i
                                                    class="bi bi-eye me-2"></i>View</a>
                                        @endcan
                                        @can('Ticket Edit')
                                            @if ($item->status !== 'completed')
                                                <a class="dropdown-item  text-warning"
                                                    href='{{ route("$route.edit", $item->id) }}'><i
                                                        class="bi bi-pencil-square me-2"></i>Edit</a>
                                            @endif
                                        @endcan
                                        @can('Ticket Delete')
                                            @php $url = "ticket/$item->id" @endphp
                                            <button onclick='openConfirmation("{{ url($url) }}")' type="button"
                                                class="dropdown-item text-danger"><i
                                                    class="bi bi-trash me-2"></i>Delete</button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->ticket_no }}</td>
                            <td>
                                <a href="{{ route('customer-user.show', $item->customer_id) }}">{{ $item->name }}</a>
                            </td>
                            <td>{{ $item->phone }}</td>
                            <td>{{ $item->created_at->format('d-m-y h:m a') }}</td>
                            <td>{{ $item->priority }}</td>
                            <td>{{ $item->ticket_category->name }}
                                <br>
                                <small>{{ $item->ticket_category->priority }}</small>
                            </td>
                            <td>
                                <form action="{{ route('ticket.show', $item->id) }}">
                                    <select name="status"
                                        class="text-white text-capitalize {{ $item->status == 'pending' ? 'bg-warning' : ($item->status == 'processing' ? 'bg-info' : 'bg-success') }}"
                                        {{ $item->status == 'completed' ? 'disabled' : '' }} name=""
                                        id="status_selection" onchange="this.form.submit()">
                                        <option {{ $item->status == 'pending' ? 'selected' : '' }} value="pending">pending
                                        </option>
                                        <option {{ $item->status == 'processing' ? 'selected' : '' }} value="processing">
                                            processing</option>
                                        <option {{ $item->status == 'completed' ? 'selected' : '' }} value="completed">
                                            completed</option>
                                    </select>
                                </form>
                            </td>
                            <td>{{ $item->updated_at->format('d-m-y h:m a') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="ml-4 data_table_pagination">{{ $data->appends(['item' => request('item')])->links() }}</div>
        </div>
    </div>
@endsection
