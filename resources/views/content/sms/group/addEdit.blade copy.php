@extends('layouts/layoutMaster')
@section('content')
<?php

use Illuminate\Support\Facades\Route;

$route = str_replace(['.index', '.store', '.create','.edit'], '', Route::currentRouteName());
$title = str_replace('.', ' ', $route);
$user_list = session('user_list');
?>
@if(isset($data)) @php $form_action = route("$route.update", $data->id); @endphp @else @php $form_action = route("$route.store"); @endphp @endif
<div class="card">
    <div class="card-body">
        <div class="text-center mb-1">
            <h3 class="mb-2 text-capitalize">{{$title}}</h3>
        </div>
        <div class="row">
            <form action="{{$form_action}}" class="row g-3" method="POST">
                @if(isset($data))
                @method('put')
                @endif
                @csrf
                <div class="col-sm-12 col-md-6">
                    <div class="border p-2">
                        <div class="mb-3">
                            {{ is_array(old('name')) ? '' : old('name') }}
                            <label class="form-label" for="name">Group Name @if($errors->has('name'))<span class="text-danger"> {{$errors->first('name')}}</span> @endif</label>
                            <input type="text" class="form-control" id="name" name="name" @if(isset($data)) value="{{ $data->name }}" @else value="{{ old('name') }}" @endif placeholder="Full Name" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="user_type">User Type @if($errors->has('user_type'))<span class="text-danger"> {{$errors->first('user_type')}}</span> @endif</label>
                            <select onchange="this.form.submit()" id="user_type" name="user_type" class="select2 form-select">
                                <option value="">Please Select One</option>
                                <option @if (old('user_type')=='home_user' ) selected @endif value="home_user">Home User</option>
                                <option @if (old('user_type')=='corporate_user' ) selected @endif value="corporate_user">Corporate User</option>
                                <option @if (old('user_type')=='dashboard_user' ) selected @endif value="dashboard_user">Dashboard User</option>
                                <option @if (old('user_type')=='active_customer' ) selected @endif value="active_customer">Active Customer</option>
                                <option @if (old('user_type')=='inactive_customer' ) selected @endif value="inactive_customer">Inactive Customer</option>
                                <option @if (old('user_type')=='pending_customer' ) selected @endif value="pending_customer">Pending Customer</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    @if(isset($user_list))
                    <div class="border p-2">
                        <h2>User List</h2>
                        @error('users')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <span>@if($errors->has('users'))<span class="text-danger"> {{$errors->first('users')}}</span> @endif</span>
                        <div class="mb-2">
                            <input class="form-check-input" type="checkbox" name="ALL" value="all" id="check-all">
                            <label class="w-50" for="check-all">ALl</label>
                        </div>
                        @foreach($user_list as $u_item)
                        <div class="form-group d-flex">
                            <input class="check-item form-check-input" type="checkbox" name="users[]" value="{{$u_item->id}}" id="{{$u_item->id}}">
                            <label class="w-50" for="{{$u_item->id}}">{{$u_item->name}} </label>
                            <label class="w-50" for="{{$u_item->id}}">{{$u_item->phone}} </label>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="col-12 text-left">
                    <div class="form-group mb-2">
                        <input type="checkbox" name="confirm" value="true" class="form-check-input" id="conf">
                        <label for="conf">Confirm Before Submit</label>
                    </div>
                    <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                    <a href='{{route("$route.index")}}' class="btn btn-warning">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('pricing-script')

<script>
    // jQuery code for handling the checkbox functionality
    $(document).ready(function() {
        // Check/uncheck all items and groups
        $('#check-all').change(function() {
            $('.check-item').prop('checked', $(this).prop('checked'));
        });
        // Check/uncheck the items within a group
        $('.check-item').change(function() {
            // Uncheck "All" if any item is unchecked
            $('#check-all').prop('checked', $('.check-item').not(':checked').length === 0);
        });

    });
</script>
@endpush