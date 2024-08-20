@extends('layouts/layoutMaster')
@section('title', "Add New Mikrotik")
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Mikrotik/</span> Add Mikrotik</h4>

<!-- Basic Layout -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{route('mikrotik-store-mikrotik')}}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="identity">Mikrotik Identity</label>
                @if($errors->has('identity'))<span class="text-danger"> {{$errors->first('identity')}}</span> @endif
                <input type="text" class="form-control" id="identity" name="identity" placeholder="Mikrotik Identity" value="{{old('identity')}}" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="host">Mikrotik IP</label>
                @if($errors->has('host'))<span class="text-danger"> {{$errors->first('host')}}</span> @endif
                <input type="text" class="form-control" id="host" name="host" placeholder="Mikrotik IP" value="{{old('host')}}" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="username">API User Name</label>
                @if($errors->has('username'))<span class="text-danger"> {{$errors->first('username')}}</span> @endif
                <input type="text" class="form-control" id="username" name="username" placeholder="API User Name" value="{{old('username')}}" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">API User Password</label>
                @if($errors->has('password'))<span class="text-danger"> {{$errors->first('password')}}</span> @endif
                <input type="text" class="form-control" id="password" name="password" placeholder="API User Password" value="{{old('password')}}" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="port">API Port</label>
                @if($errors->has('port'))<span class="text-danger"> {{$errors->first('port')}}</span> @endif
                <input type="text" class="form-control" id="port" name="port" placeholder="API Port" value="{{old('port')}}" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="sitename">Site Name</label>
                @if($errors->has('sitename'))<span class="text-danger"> {{$errors->first('sitename')}}</span> @endif
                <input type="text" class="form-control" id="sitename" name="sitename" placeholder="Site Name" value="{{old('sitename')}}" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="address">Address</label>
                @if($errors->has('address'))<span class="text-danger"> {{$errors->first('address')}}</span> @endif
                <input type="text" class="form-control" id="address" name="address" placeholder="Address" value="{{old('address')}}" />
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{route('mikrotik-view-mikrotik')}}" class="btn btn-warning">Back</a>
        </form>
    </div>
</div>
@endsection