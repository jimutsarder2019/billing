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
    <div style="display:none;" class="card mb-3 px-2 py-2">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">User / View / {{ $data->full_name }}</span>
        </h4>
    </div>
    <div class="tab-content p-1" id="myTabContent">
            @if (auth()->user()->type == 'user')
                <div class="tab-pane show active" id="grace-tab-pane" role="tabpanel" aria-labelledby="grace-tab" tabindex="0">
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
