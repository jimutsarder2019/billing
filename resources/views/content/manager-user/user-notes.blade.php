@extends('layouts/layoutMaster')
@section('title')
    Notes
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
            <span class="text-muted fw-light">Notes</span>
        </h4>
    </div>

    <div class="tab-content p-1" id="myTabContent">
            <div class="tab-pane active show" id="note-tab-pane" role="tabpanel" aria-labelledby="note-tab" tabindex="0">
                <div class="card">
                    <div class="card-body">
                        <?php
						use App\Models\CustomerEditHistory;

                        $note_histories = CustomerEditHistory::with('manager')
                            ->select('id', 'manager_id', 'subject', 'note', 'created_at')
                            ->where('customer_id', $data->id)
                            ->latest()
                            ->get();
                        ?>
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
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
                            <div class="col-sm-12 col-md-12">
                                <label for="note">Message</label>
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
