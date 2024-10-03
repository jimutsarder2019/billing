@extends('layouts/layoutMaster')
@section('title', "Edit User info | $user->username")
@section('content')
<!-- Basic Layout -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="fw-bold"><span class="text-muted fw-light">User/</span> Edit User / {{$user->username}}</h4>
    </div>
    <div class="card-body">
        <form action="{{route('customer-update-super-manager', $user->id)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-sm-12 col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="reg_date">Registration Date ( {{$user->registration_date}} )</label>
                        @if($errors->has('registration_date'))<span class="text-danger"> {{$errors->first('registration_date')}}</span> @endif
                        <input type="text" class="form-control" id="registration_date" name="registration_date" placeholder="Registration Date" value="{{$user->registration_date}}" />
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="conn_date">Connection Date ( {{$user->connection_date}} )</label>
                        @if($errors->has('conn_date'))<span class="text-danger"> {{$errors->first('conn_date')}}</span> @endif
                        <input type="text" class="form-control" id="conn_date" name="connection_date" placeholder="Connection Date" value="{{$user->connection_date}}" />
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="expire_date">expire date ( {{$user->expire_date}} )</label>
                        @if($errors->has('expire_date'))<expire_datespan class="text-danger"> {{$errors->first('expire_date')}}</expire_datespan> @endif
                        <input type="text" class="form-control" id="expire_date" name="expire_date" placeholder="expire_date" value="{{$user->expire_date}}" />
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="mb-3">
                        <label class="form-label" for="bill">Bill ( {{$user->bill}} TK )</label>
                        @if($errors->has('bill'))<billspan class="text-danger"> {{$errors->first('bill')}}</billspan> @endif
                        <input type="text" class="form-control" id="bill" name="bill" placeholder="Bill" value="{{$user->bill}}" />
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="mb-3">
                        <label class="form-label" for="status">Billing Status ( {{$user->status}} )</label>
                        @if($errors->has('status'))<statusspan class="text-danger"> {{$errors->first('status')}}</statusspan> @endif
                        <select name="status" id="" class="form-control">
                            <option {{$user->status == CUSTOMER_ACTIVE ? 'selected':''}} value="{{CUSTOMER_ACTIVE}}">{{CUSTOMER_ACTIVE}}</option>
                            <option {{$user->status == CUSTOMER_PENDING ? 'selected':''}} value="{{CUSTOMER_PENDING}}">{{CUSTOMER_PENDING}}</option>
                            <option {{$user->status == CUSTOMER_APPROVED ? 'selected':''}} value="{{CUSTOMER_APPROVED}}">{{CUSTOMER_APPROVED}}</option>
                            <option {{$user->status == CUSTOMER_SUSPENDED ? 'selected':''}} value="{{CUSTOMER_SUSPENDED}}">{{CUSTOMER_SUSPENDED}}</option>
                            <option {{$user->status == CUSTOMER_NEW_REGISTER ? 'selected':''}} value="{{CUSTOMER_NEW_REGISTER}}">{{CUSTOMER_NEW_REGISTER}}</option>
                            <option {{$user->status == CUSTOMER_EXPIRE ? 'selected':''}} value="{{CUSTOMER_EXPIRE}}">{{CUSTOMER_EXPIRE}}</option>
                            <option {{$user->status == CUSTOMER_DELETE ? 'selected':''}} value="{{CUSTOMER_DELETE}}">{{CUSTOMER_DELETE}}</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="status">Current Package</label>
                        @if($errors->has('status'))<statusspan class="text-danger"> {{$errors->first('status')}}</statusspan> @endif
                        <select name="package" id="" class="form-control">
                            @foreach($packages as $pkg_item)
                            <option {{$pkg_item->id == $user->package_id ? 'selected':''}} value="{{$pkg_item->id}}">{{$pkg_item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="status">Purchase Package</label>
                        @if($errors->has('status'))<statusspan class="text-danger"> {{$errors->first('status')}}</statusspan> @endif
                        <select name="purchase_package_id" id="" class="form-control">
                            @foreach($packages as $pkg_item)
                            <option {{$pkg_item->id == $user->purchase_package_id ? 'selected':''}} value="{{$pkg_item->id}}">{{$pkg_item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="mb-3">
                        <label class="form-label" for="wallet">wallet ( {{$user->wallet}} TK )</label>
                        @if($errors->has('wallet'))<span class="text-danger"> {{$errors->first('wallet')}}</span> @endif
                        <input type="text" class="form-control" id="wallet" name="wallet" placeholder="wallet" value="{{$user->wallet}}" />
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="mb-3">
                        <label class="form-label" for="wallet">Grace ( {{$user->allow_grace ??0}} days ) <span class="text-info">Set 0 if the use dose not have greace </span></label>
                        @if($errors->has('allow_grace'))<span class="text-danger"> {{$errors->first('allow_grace')}}</span> @endif
                        <input type="text" class="form-control" id="allow_grace" name="allow_grace" placeholder="allow_grace" value="{{$user->allow_grace}}" />
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <button onclick="return confirm('are you sure to update it')" type="submit" class="btn btn-primary">Submit</button>
                    <a href="/ispcrm_dev/public" class="btn btn-warning">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection