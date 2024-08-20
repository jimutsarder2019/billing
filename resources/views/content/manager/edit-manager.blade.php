@extends('layouts/layoutMaster')
@section('title', 'Edit Manager')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h3 class="mb-2">Edit Manager</h3>
        </div>
        <form action="{{route('managers-update-manager', $manager->id)}}" class="row g-3" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="col-12">
                        <label class="form-label mt-2" for="type">Type</label>
                        @if($errors->has('type'))<span class="text-danger"> {{$errors->first('type')}}</span> @endif
                        <select disabled id="type" name="type" class="form-control">
                            <option value="">Please Select One</option>
                            <option value="franchise" @if($manager->type == 'franchise') selected @endif>Franchise</option>
                            <option value="app_manager" @if($manager->type == 'app_manager') selected @endif>App Manager</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label mt-2 w-100" for="name">Name</label>
                        @if($errors->has('name'))<span class="text-danger"> {{$errors->first('name')}}</span> @endif
                        <input id="name" name="name" class="form-control" type="text" value="{{$manager->name}}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label mt-2 w-100" for="email">Email</label>
                        @if($errors->has('email'))<span class="text-danger"> {{$errors->first('email')}}</span> @endif
                        <input id="email" name="email" class="form-control" type="email" value="{{$manager->email}}" />
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="col-12">
                                <label class="form-label mt-2 w-100" for="password">Password</label>
                                @if($errors->has('password'))<span class="text-danger"> {{$errors->first('password')}}</span> @endif
                                <input id="password" name="password" class="form-control" type="password" />
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="col-12">
                                <label class="form-label mt-2 w-100" for="password_confirmation">Confirm Password</label>
                                @if($errors->has('password_confirmation'))<span class="text-danger"> {{$errors->first('password_confirmation')}}</span> @endif
                                <input id="password_confirmation" name="password_confirmation" class="form-control" type="password" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="col-12">
                        <label class="form-label mt-2 w-100" for="phone">Phone</label>
                        @if($errors->has('phone'))<span class="text-danger"> {{$errors->first('phone')}}</span> @endif
                        <input id="phone" name="phone" class="form-control" type="text" value="{{$manager->phone}}" />
                    </div>

                    <div class="col-12">
                        <label class="form-label mt-2" for="mikrotik_id">Mikrotik</label>
                        @if($errors->has('mikrotik_id'))<span class="text-danger"> {{$errors->first('mikrotik_id')}}</span> @endif
                        <select disabled id="mikrotik_id" name="mikrotik_id" class="form-control">
                            <option value="">Please Select One</option>
                            @foreach($mikrotiks as $mikrotik)
                            <option value="{{$mikrotik->id}}" @if($manager->mikrotik_id == $mikrotik->id) selected @endif>{{$mikrotik->identity}} | {{$mikrotik->host}}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($manager->type == FRANCHISE_MANAGER)
                    <?php
                    $package = App\Models\Package::where('mikrotik_id', $manager->mikrotik_id)->get();
                    $assigned_package = App\Models\ManagerAssignPackage::where('manager_id', auth()->user()->id)->first();
                    ?>
                    <div class="col-12">
                        <ul class='p-0'>
                            @foreach($package as $pkg)
                            <li>
                                <?php
                                $is_assigned =  App\Models\ManagerAssignPackage::where(['manager_id' => $manager->id, 'package_id' => $pkg->id])->first();
                                ?>
                                <input {{ $is_assigned  && $is_assigned->is_manager_can_add_custom_package_price == STATUS_TRUE ? 'checked' : '' }} class="form-check-input mr-2" id="{{$pkg->id}}_editable" type="checkbox" name="price_editable[]" value="{{$pkg->id}}" />
                                <label for="{{$pkg->id}}_editable">Price Editable</label>
                                <input {{ $is_assigned ? 'checked' : '' }} class="form-check-input mr-2" id="{{$pkg->id}}" type="checkbox" name="package[]" value="{{$pkg->id}}" />
                                <label for="{{$pkg->id}}">{{$pkg->name}} | {{$pkg->synonym}}</label>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label mt-2 w-100" for="address">Address</label>
                        @if($errors->has('address'))<span class="text-danger"> {{$errors->first('address')}}</span> @endif
                        <input id="address" name="address" class="form-control" type="text" value="{{$manager->address}}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label mt-2 w-100" for="grace">Grace Allowed</label>
                        @if($errors->has('grace'))<span class="text-danger"> {{$errors->first('grace')}}</span> @endif
                        <input id="grace" name="grace" class="form-control" type="number" value="{{$manager->grace_allowed}}" />
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label mt-2" for="zone_id">Zone</label>
                @if($errors->has('zones'))<span class="text-danger"> {{$errors->first('zones')}}</span> @endif
                <div class="form-group">
                    @foreach($zones as $zone)
                    <input type="checkbox" name="zones[]" value="{{ $zone->id }}" id="zone_id{{ $zone->id }}" onchange="get_subzone()" class="form-check-input" @if($manager->assingZones && in_array($zone->id, $manager->assingZones->pluck('zone_id')->toArray())) checked @endif >
                    <label for="zone_id{{ $zone->id }}">{{ $zone->name }}</label>
                    @endforeach
                </div>
            </div>
            <div class="col-12">
                <label class="form-label mt-2" for="sub_zone_id">Sub Zone</label>
                @if($errors->has('subzones'))<span class="text-danger"> {{$errors->first('subzones')}}</span> @endif
                <div class="form-group">
                    @foreach($sub_zones as $zone)
                    <input type="checkbox" name="subzones[]" value="{{ $zone->id }}" id="sub_zone_id{{ $zone->id }}" class="form-check-input" @if($manager->subzones && in_array($zone->id, $manager->subzones->pluck('subzone_id')->toArray())) checked @endif >
                    <label for="sub_zone_id{{ $zone->id }}">{{ $zone->name }}</label>
                    @endforeach
                </div>
            </div>
            <div class="col-12">
                <div id="subzones"></div>
            </div>
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                <a href="{{route('managers-manager-list')}}" class="btn btn-label-secondary btn-reset">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
<script script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function get_subzone() {
        var checkedItems = $("input:checkbox[name='zones[]']:checked");
        var checkedValues = [];

        checkedItems.each(function() {
            checkedValues.push($(this).val());
        });

        axios.post(`/get-zone-wise-subzone`, {
            'zone_ids': checkedValues
        }).then((resp) => {
            console.log(resp.data);
            if (resp.status === 200) {
                console.log(resp.data);
                let updateElement = document.getElementById('subzones');
                let option = "<div class='form-group'>";
                for (let i = 0; i < resp.data.length; i++) {
                    option += `
            <input class="form-check-input mr-2" id="sub_zone_${resp.data[i].id}" type="checkbox" name="subzones[]" value="${resp.data[i].id}" />
                <label for="sub_zone_${resp.data[i].id}">${resp.data[i].name}</label>
            `;
                }
                option += "</div>";
                updateElement.innerHTML = option;
            }
        });
    }

    window.addEventListener("load", function() {
        let expiry_date = document.getElementById('prefix');
        console.log(expiry_date);
        if (expiry_date.value == "on") {
            document.getElementById('prefix_text_field').classList.remove('d-none');
        } else {
            document.getElementById('prefix_text_field').classList.add('d-none');
        }
    })

    function showPrefixTextField(field) {
        if (field.checked == true) {
            document.getElementById('prefix_text_field').classList.remove('d-none');
        } else {
            document.getElementById('prefix_text_field').classList.add('d-none');
        }
    }
</script>