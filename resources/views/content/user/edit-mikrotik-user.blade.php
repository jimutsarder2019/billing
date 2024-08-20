@extends('layouts/layoutMaster')

@section('title','Edit mikrotik User')
@section('content')

<!-- Basic Layout -->
<div class="card mb-4">
    <div class="card-body">
        <h4 class="fw-bold mb-4"><span class="text-muted fw-light">User/</span> Edit User</h4>
        <form action="{{route('user-store-mikrotik-customer')}}" method="POST">
            @csrf
            <input type="text" id="id_in_mkt" name="id_in_mkt" value="{{$user->id_in_mkt}}" hidden readonly>
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="name">Full Name</label>
                        @if($errors->has('name'))<span class="text-danger"> {{$errors->first('name')}}</span> @endif
                        <input type="text" class="form-control" id="name" name="name" value="{{old('name')}}" placeholder="Full Name" />
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="gender">Gender</label>
                                @if($errors->has('gender'))<span class="text-danger"> {{$errors->first('gender')}}</span> @endif
                                <select id="gender" name="gender" class="form-control">
                                    <option value="">Please Select One</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="national_id">National ID</label>
                                @if($errors->has('national_id'))<span class="text-danger"> {{$errors->first('national_id')}}</span> @endif
                                <input type="text" class="form-control" id="national_id" name="national_id" value="{{old('national_id')}}" placeholder="National Id" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        @if($errors->has('email'))<span class="text-danger"> {{$errors->first('email')}}</span> @endif
                        <input type="email" class="form-control" id="email" name="email" value="{{old('email')}}" placeholder="Email" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone No</label>
                        @if($errors->has('phone'))<span class="text-danger"> {{$errors->first('phone')}}</span> @endif
                        <input type="text" class="form-control" id="phone" name="phone" value="{{old('phone')}}" placeholder="Phone No" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="dob">Date Of Birth</label>
                        @if($errors->has('dob'))<span class="text-danger"> {{$errors->first('dob')}}</span> @endif
                        <input type="date" class="form-control" id="dob" name="dob" value="{{old('dob')}}" placeholder="Date Of Birth" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="f_name">Father Name</label>
                        @if($errors->has('f_name'))<span class="text-danger"> {{$errors->first('f_name')}}</span> @endif
                        <input type="text" class="form-control" id="f_name" name="f_name" value="{{old('f_name')}}" placeholder="Father Name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="m_name">Mother Name</label>
                        @if($errors->has('m_name'))<span class="text-danger"> {{$errors->first('m_name')}}</span> @endif
                        <input type="text" class="form-control" id="m_name" name="m_name" value="{{old('m_name')}}" placeholder="Mother Name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="address">Address</label>
                        @if($errors->has('address'))<span class="text-danger"> {{$errors->first('address')}}</span> @endif
                        <input type="text" class="form-control" id="address" name="address" value="{{old('address')}}" placeholder="Address" />
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-6">
                                <label class="form-label" for="zone_id">Zone</label>
                                @if($errors->has('zone_id'))<span class="text-danger"> {{$errors->first('zone_id')}}</span> @endif
                                <select id="zone_id" name="zone_id" class="select2 form-select" onchange="get_zonewise_subzone()">
                                    <option value="">Please Select One</option>
                                    @foreach($zones as $zone)
                                    <option value="{{$zone->id}}">{{$zone->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label class="form-label" for="sub_zone_id">Sub Zone</label>
                                @if($errors->has('sub_zone_id'))<span class="text-danger"> {{$errors->first('sub_zone_id')}}</span> @endif
                                <select id="sub_zone_id" value="{{old('sub_zone_id')}}" name="sub_zone_id" class="select2 form-select">
                                    <option value="">Please Select One</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        @if($errors->has('reg_date'))<span class="text-danger"> {{$errors->first('reg_date')}}</span> @endif
                        <label class="form-label" for="reg_date">Registration Date</label>
                        <input type="datetime-local" class="form-control" id="reg_date" name="reg_date" value="{{old('reg_date')}}" placeholder="Registration Date" />
                    </div>
                    <div class="mb-3">
                        @if($errors->has('conn_date'))<span class="text-danger"> {{$errors->first('conn_date')}}</span> @endif
                        <label class="form-label" for="conn_date">Connection Date</label>
                        <input type="datetime-local" class="form-control" id="conn_date" name="conn_date" value="{{old('conn_date')}}" placeholder="Connection Date" />
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="mikrotik_id">Mikrotik</label>
                        <select disabled id="mikrotik_id" name="mikrotik_id" class="form-control">
                            <option value="">Please Select One</option>
                            @foreach($mikrotiks as $mikrotik)
                            <option value="{{$mikrotik->id}}" @if($user->mikrotik_id == $mikrotik->id) selected @endif>{{$mikrotik->identity}}</option>
                            @endforeach
                        </select>
                    </div>
                    <?php
                    // echo $user;
                    $customer_package = App\Models\Package::where(['name' => $user->profile, 'mikrotik_id' => $user->mikrotik_id])->first();
                    // $customer_package = App\Models\Package::where('name', $user->profile)->first();
                    ?>
                    @if (auth()->user()->type == FRANCHISE_MANAGER)
                    <div class="mb-3">
                        <?php
                        $manager_assing_package = App\Models\ManagerAssignPackage::where(['package_id' => $customer_package->id, 'manager_id' => auth()->user()->id])->first();
                        if ($manager_assing_package) {
                            $bill = $manager_assing_package->manager_custom_price !== null ? $manager_assing_package->manager_custom_price : $customer_package->franchise_price;
                        } else {
                            $bill = 0;
                            echo '<div class="alert alert-warning">manager_assing_package not found</div>';
                        }
                        ?>
                        <label class="form-label" for="package_id">Package {{': '.$customer_package->name. ' |   Price:'.$customer_package->price ?? 'null'}} TK</label>
                        <input type="text" id="package_id" name="package_id" value="{{$customer_package->price !== null ? $customer_package->id :null}}" hidden readonly>
                        @if($errors->has('package_id'))<span class="text-danger"> {{$errors->first('package_id')}}</span> @endif
                        <select disabled name="package" class="form-control">
                            @foreach($packages as $package)
                            @if($customer_package && $customer_package->id == $package->id )
                            <option @if($customer_package && $customer_package->id == $package->id) selected @endif value="{{$package->id}}">{{$package->name}}</option>
                            @endif
                            @endforeach
                        </select>
                        <div class="mb-3">
                            <label class="form-label" for="bill">Bill</label>
                            @if($errors->has('bill'))<span class="text-danger"> {{$errors->first('bill')}}</span> @endif
                            <input readonly type="number" class="form-control bg-light" id="bill" name="bill" value="{{$bill}}" placeholder="Bill" />
                        </div>
                    </div>
                    @else
                    @if($customer_package)
                    <label class="form-label" for="package_id">Package {{': '.$customer_package->name. ' |   Price:'.$customer_package->price ?? 'null'}} TK </label>
                    <input type="text" id="package_id" name="package_id" value="{{$customer_package->price !== null ? $customer_package->id :null}}" hidden readonly>
                    @else
                    <div class="alert alert-warning">Package not found</div>
                    @endif
                    @if($errors->has('package_id'))
                    <br><span class="text-danger"> {{$errors->first('package_id')}}</span>
                    @endif
                    <select disabled name="package" class="form-control bg-light">
                        @foreach($packages as $package)
                        @if($customer_package && $customer_package->id == $package->id )
                        <option @if( $customer_package && $customer_package->id == $package->id) selected @endif value="{{$package->id}}">{{$package->name}}</option>
                        @else
                        <option value="">Something want worng to select this package</option>
                        @endif
                        @endforeach
                    </select>
                    @endif
                    <div class="row mt-2">
                        @if (auth()->user()->type !== FRANCHISE_MANAGER)
                        <div class="col-sm-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="bill">Bill</label>
                                @if($errors->has('bill'))<span class="text-danger"> {{$errors->first('bill')}}</span> @endif
                                <input readonly type="number" class="form-control bg-light" id="bill" name="bill" value="{{$customer_package && $customer_package->price ? $customer_package->price :0 }}" placeholder="Bill" />
                            </div>
                        </div>
                        @endif
                        @can('Edit Customer Discount')
                        <div class="col-sm-12 col-md-{{auth()->user()->type !== FRANCHISE_MANAGER ? '6' :'12'}}">
                            <div class="mb-3">
                                <label class="form-label" for="discount">Discount </label>
                                <p class="p-0 m-0"><small class="text-success">(will Apply this if remaining days has gretter then 30 days or next month)</small></p>
                                @if($errors->has('discount'))<span class="text-danger"> {{$errors->first('discount')}}</span> @endif
                                <input type="number" class="form-control" id="discount" name="discount" value="{{old('discount')}}" placeholder="Discount" />
                            </div>
                        </div>
                        @endcan
                    </div>
                    <div class="mb-3">
                        @if($errors->has('expire_date'))<span class="text-danger"> {{$errors->first('expire_date')}}</span> @endif
                        <label class="form-label" for="expire_date">Custom Expire Date</label>
                        <input type="datetime-local" class="form-control" id="expire_date" name="expire_date" value="{{old('expire_date')}}" placeholder="Expire Date" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="username">User Name</label>
                        <input readonly type="text" class="form-control bg-light" id="username" name="username" placeholder="User Name" value="{{$user->name}}" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input readonly type="text" class="form-control bg-light" id="password" name="password" placeholder="Pssword" value="{{$user->password}}" />
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function get_zonewise_subzone() {
        let zone_id = document.getElementById('zone_id').value;
        let app_url = document.head.querySelector('meta[name="app_url"]').content;
        axios.get(`${app_url}/get-zonewise-subzone/${zone_id}`).then((resp) => {
            if (resp.status == 200) {
                let sub_zone = document.getElementById('sub_zone_id');
                let option = "";
                for (let i = 0; resp.data.subzone.length > i; i++) {
                    option = option.concat(`<option value=${resp.data.subzone[i].id}> ${resp.data.subzone[i].name} </option>`)
                }
                sub_zone.innerHTML = option;
            }
        })
    }
</script>