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
        <div class="tab-pane fade {{ $errors->any() ? '' : 'show active' }}" id="invoice-tab-pane" role="tabpanel"
            aria-labelledby="invoice-tab" tabindex="0">
            <!-- Invoice table -->
            <div class="mb-4">
                <h5 class="card-header">Invoice</h5>
                <div class="card-datatable table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th style="display:none"><i class="fa fa-cogs"></i></th>
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
                                    <td style="display:none">
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
