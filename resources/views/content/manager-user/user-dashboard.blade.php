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
    <div class="row">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <!-- User Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="user-avatar-section">
                        <div class=" d-flex align-items-center flex-column">
                            <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                src='{{ $data->avater ? asset($data->avater) : asset(MANAGER_DEFAULT_LOG) }}' height="100"
                                width="100" alt="{{ $data->profile_photo_url }}" />
                            <div class="user-info text-center">
                                <h4 class="mb-2">{{ $data->full_name }}</h4>
                                <span class="badge bg-label-secondary mt-1">Customer</span>
                                <br>
                                <p class="pt-2"> Bill: {{ $data->bill }} ৳</p>
                                @if ($data->allow_grace)
                                    <span class="badge bg-label-warning mt-1">Grace : {{ $data->allow_grace }} Days</span>
                                @endif
                                <span>{{ $data->phone }}</span>
                                <div class="mt-2">
                                    Added By :
                                    <span class="badge bg-label-secondary mt-1">{{ $data->manager->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-container pt-4">
                        <div class="d-flex justify-content-center">
						    <span style="margin-right:5px">Account Status:</span> 
                            <a title="Click here to {{ $data->mikrotik_disabled == STATUS_TRUE ? 'Enable' : 'Disable' }} this user"
                                href="javascript:void(0)"
                                class="btn btn-sm btn-label-{{ $data->mikrotik_disabled == STATUS_TRUE ? 'warning' : 'success' }}">{{ $data->mikrotik_disabled == STATUS_TRUE ? 'Disable' : 'Enable' }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ User Sidebar -->
        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <div class="row mb-3">
                <div class="col-sm-12 col-md-6">
                    <div class="card p-2">
                        <small class="text-center mb-2 font-semibold">Customer & Billing Information</small>
                        <table class="border">
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Full Name</small></th>
                                <th class="border ps-2"><small>{{ $data->full_name }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Balance</small></th>
                                <th class="border ps-2"><small>{{ $data->wallet ?? 00 }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Package</small></th>
                                <th class="border ps-2"><small>{{ $data->package->name }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Bill</small></th>
                                <th class="d-flex ps-2">
                                    <small class="w-50 pe-1">{{ $data->bill }} ৳</small>
                                    <small class="w-50 border-start ps-1">Discount: {{ $data->discount ?? 00 }} ৳</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Expire Date</small></th>
                                <th class="border ps-2 text-success">
                                    <small>{{ \Carbon\Carbon::parse($data->expire_date)->format('Y-m-d h:i:s A') }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Grace</small></th>
                                <th class="border ps-2"><small>{{ $data->allow_grace ?? 0 }} Days</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Billing Status:</small></th>
                                <th class="border ps-2"><small class="text-capitalize">{{ $data->status }}</small></th>
                            </tr>


                        </table>
                    </div>
                    <div class="card p-2 mt-3">
                        <small class="text-center mb-2 font-semibold">Server Information</small>
                        <table class="border">
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Server</small></th>
                                <th class="border ps-2"><small>{{ $data->service }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Username</small></th>
                                <th class="border ps-2"><small>{{ $data->username }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Password</small></th>
                                <th class="border ps-2"><small>{{ $data->password }}</small></th>
                            </tr>
                            <tr style="display:none">
                                <th class="bg-lighten w-40 border ps-2"><small>Mikrotik:</small></th>
                                <th class="border ps-2">
                                    <small>{{ isset($data->mikrotik) ? $data->mikrotik->identity : '' }} |
                                        {{ $data->mikrotik ? $data->mikrotik->host : '' }} </small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Active Mac:</small></th>
                                <th class="border ps-2">
                                    <small> {{ $mkt_data['caller-id'] ?? '' }}<i class="fa fa-server"></i></small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Uptime:</small></th>
                                <th class="border ps-2">
                                    <small>{{ $mkt_data['uptime'] ?? '' }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Last Logout:</small></th>
                                <th class="border ps-2"><small>
                                        {{ $mkt_data['last-logged-out'] ?? '' }}
                                    </small>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card p-2 mt-1">
                        <small class="text-center mb-2 font-semibold">Basic Information</small>
                        <table class="border">
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Father Name</small></th>
                                <th class="border ps-2"><small>{{ $data->father_name ?? 'N\A' }}</small></th>
                            </tr>

                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Mother Name</small></th>
                                <th class="border ps-2"><small>{{ $data->mother_name ?? 'N\A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Address</small></th>
                                <th class="border ps-2"><small>{{ $data->address ?? 'N\A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Phone</small></th>
                                <th class="border ps-2"><small>{{ $data->phone }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Email</small></th>
                                <th class="border ps-2"><small>{{ $data->Email ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Gender</small></th>
                                <th class="border ps-2"><small>{{ $data->gender ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>NID</small></th>
                                <th class="border ps-2"><small>{{ $data->national_id ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Date Of Birth</small></th>
                                <th class="border ps-2"><small>{{ $data->date_of_birth ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Zone</small></th>
                                <th class="border ps-2"><small>{{ $data->zone->name ?? 'N/A' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Sub Zone</small></th>
                                <th class="border ps-2"><small>{{ $data->sub_zone->name ?? 'N/A' }}</small></th>
                            </tr>

                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Purchase Package</small></th>
                                <th class="border ps-2"><small>{{ $data->purchase_package->name ?? 'N/A' }}</small></th>
                            </tr>

                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Send Sms</small></th>
                                <th class="border ps-2"><small>{{ $data->is_send_sms == 1 ? 'Yes' : 'NO' }}</small></th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Registration Date</small></th>
                                <th class="border ps-2">
                                    <small>{{ \Carbon\Carbon::parse($data->registration_date)->format('Y-m-d h:i:s A') }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Connection Date</small></th>
                                <th class="border ps-2">
                                    <small>{{ \Carbon\Carbon::parse($data->connection_date)->format('Y-m-d h:i:s A') }}</small>
                                </th>
                            </tr>
                            <tr>
                                <th class="bg-lighten w-40 border ps-2"><small>Manager</small></th>
                                <th class="border ps-2"><small>{{ $data->manager->name }}</small></th>
                            </tr>
                            <tr style="display:none">
                                <th class="bg-lighten w-40 border ps-2"><small>Disabled In Mikrotik</small></th>
                                <th class="border ps-2">
                                    <small>{{ $data->mikrotik_disabled == 0 ? 'False' : 'True' }}</small>
                                </th>
                            </tr>


                        </table>
                    </div>
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
