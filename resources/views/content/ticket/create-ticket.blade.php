@extends('layouts/layoutMaster')
@section('title')
    New Ticket
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="text-center mb-1">
                <h3 class="mb-2 text-capitalize">New Ticket</h3>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <form action="{{ route('ticket.store') }}" class="row g-3" method="POST">
                        @csrf
                        <div class="col-12">
                            <div class="form-group">
                                <select name="customer" id="customer" class="select2 form-select"
                                    onchange="changeCustomer()">
                                    <option value="">-----Select Customer----</option>
                                    <?php $customers = App\Models\Customer::select('id', 'username', 'phone')->get(); ?>
                                    @foreach ($customers as $c_items)
                                        <option value="{{ $c_items->id }}">{{ $c_items->username }} | {{ $c_items->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                <label class="form-label w-100" for="name">name</label>
                                <input id="c_name" name="name"
                                    @if (isset($data)) value="{{ $data->name }}" @else value="{{ old('name') }}" @endif
                                    placeholder="Name" class="form-control" type="text" />
                                <label class="form-label w-100" for="phone">phone</label>
                                <input id="c_phone" name="phone"
                                    @if (isset($data)) value="{{ $data->phone }}" @else value="{{ old('phone') }}" @endif
                                    placeholder="Phone" class="form-control" type="text" />
                            </div>
                            <div class="form-group">
                                <label class="form-label w-100" for="division">category @if ($errors->has('category'))
                                        <span class="text-danger"> {{ $errors->first('category') }}</span>
                                    @endif
                                </label>
                                <select name="category" id="category" class="form-control" onchange="changeCategory()">
                                    <option value="">------Select Category------</option>
                                    <?php $categorys = App\Models\TicketCategory::select('id', 'name', 'priority')->get(); ?>
                                    @foreach ($categorys as $cat_item)
                                        <option value="{{ $cat_item->id }}">{{ $cat_item->name }} |
                                            {{ $cat_item->priority }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label w-100" for="division">Priority @if ($errors->has('priority'))
                                        <span class="text-danger"> {{ $errors->first('priority') }}</span>
                                    @endif
                                </label>
                                <select name="priority" id="priority" class="form-control">
                                    <option value="">Select</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label w-100" for="note">Note @if ($errors->has('note'))
                                        <span class="text-danger"> {{ $errors->first('note') }}</span>
                                    @endif
                                </label>
                                <textarea name="note" id="" cols="30" rows="5" placeholder="Write Note" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-12 text-left">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                            <a href='{{ route('ticket.index') }}' class="btn btn-warning">Close</a>
                        </div>
                    </form>
                </div>

                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="card-title m-0 me-2 pt-1 mb-2">Activity Timeline</h5>
                            <div class="dropdown">
                                <div data-bs-toggle="tooltip" data-bs-title="Total Tickets" id="item_total"
                                    class="badge bg-primary"></div>
                                <div data-bs-toggle="tooltip" data-bs-title="Tickets Pending" id="item_pending"
                                    class="badge bg-warning"></div>
                                <div data-bs-toggle="tooltip" data-bs-title="Processing Tickets" id="item_processing"
                                    class="badge bg-info"></div>
                                <div data-bs-toggle="tooltip" data-bs-title="Total Completed" id="item_success"
                                    class="badge bg-success"></div>
                            </div>
                        </div>
                        <div class="card-body pb-0">
                            <ul class="timeline ms-1 mb-0" id="prev_tkt_content"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('pricing-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"
        integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function changeCustomer() {
            const customer_id = document.getElementById('customer').value
            var selectedText = $("#customer option:selected").text().split("|");
            document.getElementById("c_name").value = selectedText[0];
            document.getElementById("c_phone").value = selectedText[1];
            let app_url = document.head.querySelector('meta[name="app_url"]').content;
            axios.get(`${app_url}/get-customer-ticket/${customer_id}`, {})
                .then((resp) => {
                    if (resp.data.success) {
                        var html = ''; // Initialize the HTML content
                        var pending = 0
                        var complete = 0
                        var processing = 0
                        resp.data.data.forEach(function(item) {
                            var statusClass = '';
                            if (item.status === 'pending') {
                                statusClass = 'warning';
                                pending += 1;
                            } else if (item.status === 'processing') {
                                statusClass = 'info';
                                processing += 1;
                            } else {
                                statusClass = 'success';
                                complete += 1;
                            }
                            // Parse the date string with Moment.js
                            let momentDate = moment(item.created_at);

                            // Format the date string as "d-m-t m:i a"
                            let formattedDateStr = momentDate.format("DD-MM-YYYY hh:mm A");

                            html += `
                    <li class="timeline-item timeline-item-transparent ps-4">
                    <span class="timeline-point timeline-point-${statusClass}"></span> <div class="timeline-event">
                            <div class="timeline-header">
                                <h6 class="mb-0">${item.name}</h6>
                                <small class="text-muted">${formattedDateStr}</small>
                            </div>
                            <small class="mb-0">${item.priority} </small>
                            <p class="mb-0">${item.note}</p>
                        </div>
                    </li>`;
                        });
                        // <small class="text-capitalize badge bg-${statusClass}">${item.status}</small>
                        // Update the content of the 'prev_tkt_content' element with the generated HTML
                        document.getElementById('prev_tkt_content').innerHTML = html;
                        document.getElementById('item_total').innerHTML = resp.data.data.length;
                        document.getElementById('item_pending').innerHTML = pending;
                        document.getElementById('item_processing').innerHTML = processing;
                        document.getElementById('item_success').innerHTML = complete;
                    }
                })
                .catch(e => {
                    console.error(e);
                });
        }

        function changeCategory() {
            var selectedText = $("#category option:selected").text().split("|");
            const priority = selectedText[1].replace(/\s/g, '')
            $("#priority option[value='" + priority + "']").prop("selected", true);
        }
    </script>
@endpush
