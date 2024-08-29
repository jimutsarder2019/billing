@extends('layouts/layoutMaster')
@section('title', 'User New Registration')
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">User/</span> Add User</h4>

<!-- Basic Layout -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{route('customer-user.store')}}" method="POST">
            @csrf
            <div class="row">
                <div class="col-sm-12 col-md-8 m-auto">
                    <div class="mb-3">
                        <label class="form-label" for="name">Full Name</label>
                        @if($errors->has('name'))<span class="text-danger"> {{$errors->first('name')}}</span> @endif
                        <input type="text" class="form-control" id="name" value="{{old('name')}}" name="name" placeholder="Full Name" />
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-6 form-group">
                            <label class="form-label" for="gender">Gender</label>
                            @if($errors->has('gender'))<span class="text-danger"> {{$errors->first('gender')}}</span> @endif
                            <select id="gender" name="gender" class="select2 form-select form-control">
                                <option value="">Please Select One</option>
                                <option {{old('gender') =='Male' ?'selected' :''}} value="Male">Male</option>
                                <option {{old('gender') =='Female' ?'selected' :''}} value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-sm-12 col-md-6 form-group">
                            <label class="form-label" for="dob">Date Of Birth</label>
                            @if($errors->has('dob'))<span class="text-danger"> {{$errors->first('dob')}}</span> @endif
                            <input type="date" class="form-control" id="dob" value="{{old('dob')}}" name="dob" placeholder="Date Of Birth" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="national_id">National ID</label>
                        @if($errors->has('national_id'))<span class="text-danger"> {{$errors->first('national_id')}}</span> @endif
                        <input type="text" class="form-control" id="national_id" value="{{old('national_id')}}" name="national_id" placeholder="National Id" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        @if($errors->has('email'))<span class="text-danger"> {{$errors->first('email')}}</span> @endif
                        <input type="email" class="form-control" id="email" value="{{old('email')}}" name="email" placeholder="Email" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone No</label>
                        @if($errors->has('phone'))<span class="text-danger"> {{$errors->first('phone')}}</span> @endif
                        <input type="text" class="form-control" id="phone" value="{{old('phone')}}" name="phone" placeholder="Phone No" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="f_name">Father Name</label>
                        @if($errors->has('f_name'))<span class="text-danger"> {{$errors->first('f_name')}}</span> @endif
                        <input type="text" class="form-control" id="f_name" value="{{old('f_name')}}" name="f_name" placeholder="Father Name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="m_name">Mother Name</label>
                        @if($errors->has('m_name'))<span class="text-danger"> {{$errors->first('m_name')}}</span> @endif
                        <input type="text" class="form-control" id="m_name" value="{{old('m_name')}}" name="m_name" placeholder="Mother Name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="address">Address</label>
                        @if($errors->has('address'))<span class="text-danger"> {{$errors->first('address')}}</span> @endif
                        <input type="text" class="form-control" id="address" value="{{old('address')}}" name="address" placeholder="Address" />
                    </div>
                    <div class="mb-3 row">
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="form-label" for="zone_id">Zone</label>
                            @if($errors->has('zone_id'))<span class="text-danger"> {{$errors->first('zone_id')}}</span> @endif
                            <select id="zone_id" value="{{old('zone_id')}}" name="zone_id" class="select2 form-select">
                                <option value="">Please Select One</option>
                                @foreach($zones as $zone)
                                <option value="{{$zone->id}}" {{old('zone_id') == $zone->id ?'selected' :''}}>{{$zone->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="form-label" for="sub_zone_id">Sub Zone</label>
                            @if($errors->has('sub_zone_id'))<span class="text-danger"> {{$errors->first('sub_zone_id')}}</span> @endif
                            <select id="sub_zone_id" value="{{old('sub_zone_id')}}" name="sub_zone_id" class="select2 form-select">
                                <option value="">Please Select One</option>
                                @foreach($subzones as $subzone)
                                <option {{old('sub_zone_id') == $subzone->id ?'selected' :''}} value="{{$subzone->id}}">{{$subzone->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="form-label" for="reg_date">Registration Date</label>
                            @if($errors->has('reg_date'))<span class="text-danger"> {{$errors->first('reg_date')}}</span> @endif
                            <input type="date" class="form-control" id="reg_date" name="reg_date" value="{{old('reg_date')}}" placeholder="Registration Date" />
                        </div>
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="form-label" for="conn_date">Connection Date</label>
                            @if($errors->has('conn_date'))<span class="text-danger"> {{$errors->first('conn_date')}}</span> @endif
                            <input type="date" class="form-control" id="conn_date" name="conn_date" value="{{old('conn_date')}}" placeholder="Connection Date" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="package_id">Package</label>
                        @if($errors->has('package_id'))<span class="text-danger"> {{$errors->first('package_id')}}</span> @endif

                        @if(auth()->user()->type == FRANCHISE_MANAGER)
                        <?php
                        $assigned_package = App\Models\ManagerAssignPackage::with('package')->where('manager_id', auth()->user()->id)->get();
                        ?>
                        <select id="package_id" name="package_id" class="select2 form-select" onchange="addPriceToBillField()">
                            <option value="">Please Select One</option>
                            @foreach($assigned_package as $asg_pkg)
                            <option {{old('package_id') == $asg_pkg->package->id ? 'selected' :''}} value="{{$asg_pkg->package->id}}">{{$asg_pkg->package->name}} | {{$asg_pkg->franchise_price ?? 0}} TK</option>
                            @endforeach
                        </select>
                        @else
                        <select id="package_id" name="package_id" class="select2 form-select" onchange="addPriceToBillField()">
                            <option value="">Please Select One</option>
                            @foreach($packages as $package)
                            <option {{old('package_id') == $package->id ?'selected' :''}} value="{{$package->id}}">{{$package->name}} | {{$package->price ?? 0}} Tk</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div class="mb-3 row">
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="form-label" for="bill">Bill</label>
                            @if($errors->has('bill'))<span class="text-danger"> {{$errors->first('bill')}}</span> @endif
                            <input type="number" class="form-control" id="bill" value="{{old('bill')}}" name="bill" placeholder="Bill" readonly />
                        </div>
                        @can('New Customer Discount')
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="form-label" for="discount">Discount</label>
                            @if($errors->has('discount'))<span class="text-danger"> {{$errors->first('discount')}}</span> @endif
                            <input type="number" class="form-control" id="discount" value="{{old('discount')}}" name="discount" placeholder="Discount" />
                        </div>
                        @endcan 
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    window.addEventListener("load", function() {
        let reg_date = document.getElementById('reg_date');
        const timeElapsed = Date.now();
        const today = new Date(timeElapsed);
        reg_date.value = today.toLocaleDateString();
    });

    function addPriceToBillField() {
        let package_id = document.getElementById('package_id').value;
        let app_url = document.head.querySelector('meta[name="app_url"]').content;
        axios.get(`${app_url}/user/get-package-details/${package_id}`).then((resp) => {
            if (resp.status == 200) {
                let bill_field = document.getElementById('bill')
                bill_field.value = resp.data.bill;
            }
        })
    }
</script>