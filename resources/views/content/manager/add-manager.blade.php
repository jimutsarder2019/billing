@extends('layouts/layoutMaster')
@section('title','Add Manager')
@section('content')
<div class="row">
    <div class="col-sm-12 col-md-8 m-auto">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h3 class="mb-2">Add Manager</h3>
                </div>
                <form action="{{route('managers-store-manager')}}" class="row g-3" method="POST">
                    @csrf
                    <div class="col-12">
                        <label class="form-label" for="manager_type">Type</label>
                        @if($errors->has('type'))<span class="text-danger"> {{$errors->first('type')}}</span> @endif
                        <select required id="manager_type" name="type" onchange="change_manager_type(this)" class="form-control">
                            <option value="">Please Select One</option>
                            <option {{old('type') == 'franchise' ? 'selected':''}} value="franchise">Franchise</option>
                            <option {{old('type') == 'app_manager' ? 'selected':''}} value="app_manager">App Manager</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="name">Name</label>
                        @if($errors->has('name'))<span class="text-danger"> {{$errors->first('name')}}</span> @endif
                        <input required id="name" name="name" class="form-control" value="{{old('name')}}" type="text" />
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="email">Email</label>
                        @if($errors->has('email'))<span class="text-danger"> {{$errors->first('email')}}</span> @endif
                        <input required id="email" name="email" class="form-control" type="email" value="{{old('email')}}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="password">Password</label>
                        @if($errors->has('password'))<span class="text-danger"> {{$errors->first('password')}}</span> @endif
                        <input required id="password" name="password" class="form-control" type="text" value="{{old('old')}}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="password_c">Confirm Password</label>
                        @if($errors->has('password_confirmation'))<span class="text-danger"> {{$errors->first('password_confirmation')}}</span> @endif
                        <input required id="password_c" name="password_confirmation" class="form-control" type="text" value="{{old('password_confirmation')}}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="phone">Phone</label>
                        @if($errors->has('phone'))<span class="text-danger"> {{$errors->first('phone')}}</span> @endif
                        <input required id="phone" name="phone" class="form-control" type="text" value="{{old('phone')}}" />
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="mkt_id">Mikrotik</label>
                        @if($errors->has('mikrotik_id'))<span class="text-danger"> {{$errors->first('mikrotik_id')}}</span> @endif
                        <select required id="mkt_id" name="mikrotik_id" onchange="get_mikrotik_package()" class="form-control">
                            <option value="">Please Select One</option>
                            @foreach($mikrotiks as $mikrotik)
                            <option {{old('mikrotik_id') == $mikrotik->id ? 'selected':''}} value="{{$mikrotik->id}}">{{$mikrotik->identity}} | {{$mikrotik->host}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div id="package_id" class=""></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label w-100" for="address">Address</label>
                        @if($errors->has('address'))<span class="text-danger"> {{$errors->first('address')}}</span> @endif
                        <input required id="address" name="address" class="form-control" type="text" value="{{old('address')}}" />
                    </div>
                    <div class="col-12" id="allow_grace_filed">
                        @php
                        $grace = App\Models\AdminSetting::where('slug','grace')->first() ;
                        @endphp
                        <label class="form-label w-100" for="grace">Grace Allowed {{$grace ? '('. $grace->value . ')' : ''}}</label>
                        @if($errors->has('grace'))<span class="text-danger"> {{$errors->first('grace')}}</span> @endif
                        <input id="grace" name="grace" class="form-control" type="number" min="1" max="{{$grace ? $grace->value : ''}}" value="{{old('grace')}}" />
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="zones">Zone</label>
                        @if($errors->has('zones'))<span class="text-danger"> {{$errors->first('zones')}}</span> @endif
                        <div class="form-group">
                            @foreach($zones as $zone)
                            <input type="checkbox" name="zones[]" value="{{$zone->id}}" onchange="get_subzone()" id="zone_id{{$zone->id}}" class="form-check-input">
                            <label for="zone_id{{$zone->id}}">{{$zone->name}}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="subzones"></div>
                        <!-- <label class="form-label" for="subzone_id">Sub Zone</label>
                        @if($errors->has('subzone_id'))<span class="text-danger"> {{$errors->first('subzone_id')}}</span> @endif
                        <select id="subzone_id" name="subzone_id" class="select2 form-select">
                            <option value="">Please Select One</option>
                            @foreach($sub_zones as $sub_zone)
                            <option value="{{$sub_zone->id}}">{{$sub_zone->name}}</option>
                            @endforeach
                        </select> -->
                    </div>
                    <!-- <div class="col-12">
                <label class="form-label w-100" for="prefix">Prefix</label>
                <input class="form-check-input" id="prefix" name="prefix" type="checkbox" onchange="togglePrefixTextField(this)" />
            </div>
            <div id="prefix_text_field_toggle" class="col-12 d-none">
                <label class="form-label w-100" for="prefix_text">Prefix Text</label>
                <input id="prefix_text" name="prefix_text" class="form-control" type="text" />
            </div> -->
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href="{{route('managers-manager-list')}}" class="btn btn-label-secondary btn-reset">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

<script script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    // jQuery code for handling the checkbox functionality
    // $(document).ready(function() {
    // function togglePrefixTextField(field) {
    //     if (field.checked == true) {
    //         document.getElementById('prefix_text_field_toggle').classList.remove('d-none');
    //     } else {
    //         document.getElementById('prefix_text_field_toggle').classList.add('d-none');
    //     }
    // }
    // /get_mikrotik_package
    function change_manager_type(event) {
        if (event.value !== 'app_manager') {
            document.getElementById('allow_grace_filed').classList.add('d-none')
        } else {
            document.getElementById('allow_grace_filed').classList.remove('d-none')

        }
    }

    function get_mikrotik_package() {
        let manager_type = document.getElementById('manager_type').value;
        if (manager_type == 'franchise') {
            let id = document.getElementById('mkt_id').value;
            let app_url = document.head.querySelector('meta[name="app_url"]').content;
            axios.get(`${app_url}/mikrotik-package/${id}`).then((resp) => {
                if (resp.status === 200) {
                    let updateElement = document.getElementById('package_id');
                    let option = "<ul class='p-0'>";
                    for (let i = 0; i < resp.data.package.length; i++) {
                        option += `
                    <li>
                    <input class="form-check-input mr-2" id="${resp.data.package[i].id}_editable" type="checkbox" name="price_editable[]" value="${resp.data.package[i].id}" />
                    <label for="${resp.data.package[i].id}_editable">Price Editable</label>
                    <input class="form-check-input mr-2" id="${resp.data.package[i].id}" type="checkbox" name="package[]" value="${resp.data.package[i].id}" />
                        <label for="${resp.data.package[i].id}">${resp.data.package[i].name} | ${resp.data.package[i].synonym}</label>
                        </li>`;
                    }
                    option += "</ul>";
                    updateElement.innerHTML = `<h6>Package</h6>` + option;
                }
            });
        }
    }

    function get_subzone() {

        var checkedItems = $("input:checkbox[name='zones[]']:checked");
        var checkedValues = [];

        checkedItems.each(function() {
            checkedValues.push($(this).val());
        });
        console.log(checkedValues);

        let app_url = document.head.querySelector('meta[name="app_url"]').content;

        axios.post(`${app_url}/get-zone-wise-subzone`, {
            'zone_ids': checkedValues
        }).then((resp) => {
            console.log(resp.data);
            if (resp.status === 200) {
                console.log(resp.data);
                let updateElement = document.getElementById('subzones');
                let option = "<div class='form-group'>";
                for (let i = 0; i < resp.data.length; i++) {
                    option += `
                    <input class="form-check-input mr-2" id="sub_zone_${resp.data[i].id}" type="checkbox" name="sub_zones[]" value="${resp.data[i].id}" />
                        <label for="sub_zone_${resp.data[i].id}">${resp.data[i].name}</label>
                    `;
                }
                option += "</div>";
                updateElement.innerHTML = `<h6>Sub Zones</h6>` + option;
            }
        });
    }
    // });
</script>