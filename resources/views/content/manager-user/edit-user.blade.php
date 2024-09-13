@extends('layouts/layoutMaster')
@section('title', "Edit User | $user->username")
@section('content')
    <!-- Basic Layout -->
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="fw-bold mb-4"><span class="text-muted fw-light">User/</span> Edit User</h4>
            <form action="{{ route('user-change-profile', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="name">Full Name</label>
                            @if ($errors->has('name'))
                                <span class="text-danger"> {{ $errors->first('name') }}</span>
                            @endif
                            <input type="text" class="form-control" id="name" name="name" placeholder="Full Name"
                                value="{{ $user->full_name }}" />
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="gender">Gender</label>
                                    @if ($errors->has('gender'))
                                        <span class="text-danger"> {{ $errors->first('gender') }}</span>
                                    @endif
                                    <select id="gender" name="gender" class="select2 form-select">
                                        <option value="">Please Select One</option>
                                        <option value="Male" @if ($user->gender == 'Male') selected @endif>Male
                                        </option>
                                        <option value="Female" @if ($user->gender == 'Female') selected @endif>Female
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="national_id">National ID</label>
                                    @if ($errors->has('national_id'))
                                        <span class="text-danger"> {{ $errors->first('national_id') }}</span>
                                    @endif
                                    <input type="text" class="form-control" id="national_id" name="national_id"
                                        placeholder="National Id" value="{{ $user->national_id }}" />
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            @if ($errors->has('email'))
                                <span class="text-danger"> {{ $errors->first('email') }}</span>
                            @endif
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                                value="{{ $user->email }}" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">Phone No</label>
                            @if ($errors->has('phone'))
                                <span class="text-danger"> {{ $errors->first('phone') }}</span>
                            @endif
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone No"
                                value="{{ $user->phone }}" />
                            <div id="input-container" class="additional_phone">
                                @if($user->additional_phone && $user->additional_phone !== 'null')
                                    @foreach (json_decode($user->additional_phone) as $phone_item)
                                        <div class="row input-item mt-1">
                                            <div class="col-11 pe-0">
                                                <input name="additional_phone[]" placeholder="Phone" class="form-control"
                                                    value="{{ $phone_item }}" type="text" />
                                            </div>
                                            <div class="col-1 ps-0">
                                                <div class="remove-item-btn btn-close text-danger"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>  
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="dob">Date Of Birth</label>
                            @if ($errors->has('dob'))
                                <span class="text-danger"> {{ $errors->first('dob') }}</span>
                            @endif
                            <input type="date" class="form-control" id="dob" name="dob"
                                placeholder="Date Of Birth" value="{{ $user->date_of_birth }}" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f_name">Father Name</label>
                            @if ($errors->has('f_name'))
                                <span class="text-danger"> {{ $errors->first('f_name') }}</span>
                            @endif
                            <input type="text" class="form-control" id="f_name" name="f_name"
                                placeholder="Father Name" value="{{ $user->father_name }}" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="m_name">Mother Name</label>
                            @if ($errors->has('m_name'))
                                <span class="text-danger"> {{ $errors->first('m_name') }}</span>
                            @endif
                            <input type="text" class="form-control" id="m_name" name="m_name"
                                placeholder="Mother Name" value="{{ $user->mother_name }}" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="address">Address</label>
                            @if ($errors->has('address'))
                                <span class="text-danger"> {{ $errors->first('address') }}</span>
                            @endif
                            <input type="text" class="form-control" id="address" name="address"
                                placeholder="Address" value="{{ $user->address }}" />
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="zone_id">Zone</label>
                                    @if ($errors->has('zone_id'))
                                        <span class="text-danger"> {{ $errors->first('zone_id') }}</span>
                                    @endif
                                    <select id="zone_id" name="zone_id" class="form-control"
                                        onchange="get_zonewise_subzone()">
                                        <option value="">Please Select One</option>
                                        @foreach ($zones as $zone)
                                            <option value="{{ $zone->id }}"
                                                @if ($user->zone_id == $zone->id) selected @endif>{{ $zone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="sub_zone_id">Sub Zone</label>
                                    @if ($errors->has('sub_zone_id'))
                                        <span class="text-danger"> {{ $errors->first('sub_zone_id') }}</span>
                                    @endif
                                    <select id="sub_zone_id" name="sub_zone_id" class="form-control">
                                        <option value="">-------Select--------</option>
                                        @if ($subzones !== null)
                                            @foreach ($subzones as $subzone_item)
                                                <option {{ $user->sub_zone_id == $subzone_item->id ? 'selected' : '' }}
                                                    value="{{ $subzone_item->id }}">{{ $subzone_item->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="reg_date">Registration Date</label>
                                    @if ($errors->has('reg_date'))
                                        <span class="text-danger"> {{ $errors->first('reg_date') }}</span>
                                    @endif
                                    <input type="text" class="form-control" id="reg_date" name="reg_date" readonly
                                        placeholder="Registration Date" value="{{ $user->registration_date }}" />
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="conn_date">Connection Date</label>
                                    @if ($errors->has('conn_date'))
                                        <span class="text-danger"> {{ $errors->first('conn_date') }}</span>
                                    @endif
                                    <input type="datetime-local" class="form-control" id="conn_date" name="conn_date"
                                        readonly placeholder="Connection Date" value="{{ $user->connection_date }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="mikrotik_id">Mikrotik</label>
                            @if ($errors->has('mikrotik_id'))
                                <billspan class="text-danger"> {{ $errors->first('mikrotik_id') }}</billspan>
                            @endif
                            <select disabled id="mikrotik_id" name="mikrotik_id" class="form-control">
                                <option value="">Please Select One</option>
                                @foreach ($mikrotiks as $mikrotik)
                                    <option value="{{ $mikrotik->id }}"
                                        @if ($user->mikrotik_id == $mikrotik->id) selected @endif>{{ $mikrotik->identity }}
                                        {{ auth()->user()->type == APP_MANAGER ? '| ' . $mikrotik->host : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="package_id">Package</label>
                            @if ($errors->has('package_id'))
                                <span class="text-danger"> {{ $errors->first('package_id') }}</span>
                            @endif
                            <select disabled id="package_id" name="package_id" class="form-control"
                                onchange="addPriceToBillField()">
                                <option value="">Please Select One</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}"
                                        @if ($user->package_id == $package->id) selected @endif>{{ $package->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="bill">Bill</label>
                                    @if ($errors->has('package_id'))
                                        <billspan class="text-danger"> {{ $errors->first('package_id') }}</billspan>
                                    @endif
                                    <input type="text" class="form-control bg-label-secondary" id="bill"
                                        name="bill" placeholder="Bill" readonly value="{{ $user->bill }}" />
                                </div>

                            </div>
                            @can('New Customer Discount')
                                <div class="col-sm-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="discount">Discount</label>
                                        @if ($errors->has('discount'))
                                            <billspan class="text-danger"> {{ $errors->first('discount') }}</billspan>
                                        @endif
                                        <input type="number" class="form-control" id="discount" name="discount"
                                            min="1" placeholder="Discount" value="{{ $user->discount }}" />
                                    </div>
                                </div>
                            @endcan
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="username">User Name</label>
                            @if ($errors->has('username'))
                                <billspan class="text-danger"> {{ $errors->first('username') }}</billspan>
                            @endif
                            <input readonly type="text" class="form-control bg-label-secondary" id="username"
                                name="username" placeholder="User Name" value="{{ $user->username }}" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            @if ($errors->has('password'))
                                <billspan class="text-danger"> {{ $errors->first('password') }}</billspan>
                            @endif
                            <input readonly type="text" class="form-control bg-label-secondary" id="password"
                                name="password" placeholder="Pssword" value="{{ $user->password }}" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="note">Note</label>
                            <textarea name="note" class="form-control" id="note" placeholder="Note">{{ $user->note }}</textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"
    integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function get_zonewise_subzone() {
        let zone_id = document.getElementById('zone_id').value;
        let app_url = document.head.querySelector('meta[name="app_url"]').content;
        axios.get(`${app_url}/get-zonewise-subzone/${zone_id}`).then((resp) => {
            if (resp.status == 200) {
                let sub_zone = document.getElementById('sub_zone_id');
                let option = "<option value=''>----select----</option>";
                for (let i = 0; resp.data.subzone.length > i; i++) {
                    option = option.concat(
                        `<option value=${resp.data.subzone[i].id}> ${resp.data.subzone[i].name} </option>`)
                }
                sub_zone.innerHTML = option;
            }
        })
    }
</script>

@push('pricing-script')
    <script type="text/javascript">
        //new phone item_btn
        $("#new_item_btn").click(function() {
            var inputContainer = $("#input-container");
            var html = `
            <div class="row input-item mt-1">
                <div class="col-11 pe-0">
                    <input name="additional_phone[]" placeholder="Phone" class="form-control" type="text" />
                </div>
                <div class="col-1 ps-0">
                    <div class="remove-item-btn btn-close text-danger"></div>
                </div>
            </div>
        `;
            inputContainer.append(html);
        });

        // Event delegation for delete button
        $("#input-container").on("click", ".remove-item-btn", function() {
            if (!confirm('Are you sure to remove')) return
            $(this).closest('.input-item').remove();
        });
    </script>
@endpush
