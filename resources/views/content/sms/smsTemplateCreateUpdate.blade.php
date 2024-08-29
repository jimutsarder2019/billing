@extends('layouts/layoutMaster')
@section('title') sms template @endsection
@section('content')
<?php
$route = 'sms_templates';
$title = (isset($data) ? 'Edit ' : 'Add ') .  $route; ?>

@if(isset($data)) @php $form_action = route("$route.update", $data->id); @endphp @else @php $form_action = route("$route.store"); @endphp @endif
<div class="card">
    <div class="card-body">
        <div class="text-center mb-1">
            <h3 class="mb-2 text-capitalize">{{str_replace('_', ' ',$title)}}</h3>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-9">
                <form action="{{$form_action}}" class="row g-3" method="POST">
                    @if(isset($data))
                    @method('put')
                    @endif
                    @csrf
                    <div class="">
                        <label class="form-label" for="name">Name</label>
                        @if($errors->has('name')) <span class="text-danger"> {{$errors->first('name')}}</span> @endif
                        <input type="text" class="form-control" id="name" name="name" @if(isset($data)) value="{{ $data->name }}" @else value="{{ old('name') }}" @endif placeholder="Full Name" />
                    </div>
                    <div class="">
                        <label class="form-label" for="api">Select API</label>
                        @if($errors->has('api')) <span class="text-danger"> {{$errors->first('api')}}</span> @endif
                        <select id="api" name="api" class="select2 form-select">
                            <option value="">Please Select One</option>
                            @foreach($sms_apis as $api)
                            <option {{isset($data) && $data->sms_apis_id == $api->id ? 'selected' : ""}} value="{{$api->id}}">{{$api->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <label class="form-label" for="template_for">Template For</label>
                        @if($errors->has('template_for')) <span class="text-danger"> {{$errors->first('template_for')}}</span> @endif
                        <select id="template_for" name="template_for" class="select2 form-select">
                            <option value="">Please Select One</option>
                            <option {{isset($data) && $data->type == TMP_WELCOME_SMS ? 'selected' : ""}} value="{{TMP_WELCOME_SMS}}">Welcome SMS</option>
                            <option {{isset($data) && $data->type == TMP_INV_CREATE ? 'selected' : ""}} value="{{TMP_INV_CREATE}}">{{ucwords(str_replace('_',' ',TMP_INV_CREATE))}}</option>
                            <option {{isset($data) && $data->type == 'invoice_payment'? 'selected' : ""}} value="invoice_payment">Invoice Payment</option>
                            <option {{isset($data) && $data->type == 'customer_account_create'? 'selected' : ""}} value="customer_account_create">Customer Account Create</option>
                            <option {{isset($data) && $data->type == TMP_ACCOUNT_EXPIRE ? 'selected' : ""}} value="{{TMP_ACCOUNT_EXPIRE}}">{{ucwords(str_replace('_',' ',TMP_ACCOUNT_EXPIRE))}}</option>
                            <option {{isset($data) && $data->type == TMP_PACKAGE_CHANGE ? 'selected' : ""}} value="{{TMP_PACKAGE_CHANGE}}">{{ucwords(str_replace('_',' ',TMP_PACKAGE_CHANGE))}}</option>
                            <option {{isset($data) && $data->type == TMP_CUSTOMER_NEW_BALANCE ? 'selected' : ""}} value="{{TMP_CUSTOMER_NEW_BALANCE}}">{{ucwords(str_replace('_',' ',TMP_CUSTOMER_NEW_BALANCE))}}</option>
                            <option {{isset($data) && $data->type == 'user_info'? 'selected' : ""}} value="user_info">User Info</option>
                            <option {{isset($data) && $data->type == 'customer_invoice_auto_renewable'? 'selected' : ""}} value="customer_invoice_auto_renewable">customer_invoice_auto_renewable</option>
                            <option {{isset($data) && $data->type == SEND_SMS_BEFORE_CUSTOMER_EXPIRE ? 'selected' : ""}} value="{{SEND_SMS_BEFORE_CUSTOMER_EXPIRE}}">{{ucwords(str_replace('_',' ',SEND_SMS_BEFORE_CUSTOMER_EXPIRE))}}</option>
                            <option {{isset($data) && $data->type == SEND_SMS_UPDATE_EXPIRE_DATE ? 'selected' : ""}} value="{{SEND_SMS_UPDATE_EXPIRE_DATE}}">{{ucwords(str_replace('_',' ',SEND_SMS_UPDATE_EXPIRE_DATE))}}</option>
                        </select>
                    </div>
                    <div class="">
                        <label class="form-label" for="template">Template</label>
                        @if($errors->has('template')) <span class="text-danger"> {{$errors->first('template')}}</span> @endif
                        <textarea name="template" id="template" cols="30" rows="10" class="form-control">@if(isset($data)) {{$data->template}} @else {{ old('name') }} @endif</textarea>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    </div>
                </form>
            </div>
            <div class="col-sm-12 col-md-3">
                <ul>
                    <li>{customer_name} </li>
                    <li>{user_name} </li>
                    <li>{customer_user_id} </li>
                    <li>{customer_user_password} </li>
                    <li>{customer_package} </li>
                    <li>{due_amount} </li>
                    <li>{invoice_no} </li>
                    <li>{last_payment_date} </li>
                    <li>{expire_date} </li>
                    <li>{monthly_bill} </li>
                    <li>{company_name} </li>
                    <li>{company_phone_number} </li>
                    <li>{company_bkash_number} </li>
                    <li>{company_roket_number} </li>
                    <li>{company_nagad_number} </li>
                    <li>{received_amount} </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection