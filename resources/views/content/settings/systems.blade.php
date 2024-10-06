@extends('layouts/layoutMaster')
@php $route = 'settings' @endphp
<style>
    label {
        text-transform: capitalize !important;
    }
</style>
@section('title') {{'settings'}} @endsection
@section('content')
<div class="card">
    <div class="card-body">
        <!-- can('setting_view') -->
        @can('Settings edit')
        <form action='{{route("$route.store")}}' method="POST" enctype="multipart/form-data">
            @endcan
            @csrf
            <div class="row">
                <h3>Systems</h3>
                <div class="col-md-4 col-sm-12">
                    <label class="form-label" for="user_type">Disconnected Package: @if($errors->has('disconnect_package'))<span class="text-danger"> {{$errors->first('disconnect_package')}}</span> @endif</label>
                    <select id="disconnect_package" name="disconnect_package" class="select2 form-select">
                        <option value="">Please Select One</option>
                        @foreach($packages as $item)
                        <option {{ $data->where('slug', 'disconnect_package')->first() && $data->where('slug', 'disconnect_package')->first()->value == $item->name ? 'selected' : '' }} value="{{$item->name}}"> {{$item->name}} | {{$item->synonym ?? 'null'}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="name">Grace @if($errors->has('grace'))<span class="text-danger"> {{$errors->first('grace')}}</span> @endif</label>
                        <input type="number" name="grace" value="{{$data->where('slug','grace')->first() ? $data->where('slug','grace')->first()->value : ''}}" min="1" step="1" oninput="if (this.value < 1) this.value = '';" placeholder="Enter Dynamic Grace Number EX:3" class="form-control" />
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group mb-2">
                        <label class="form-label ml-1" for="create_invoice_days">Invoice Generation Days @if($errors->has('create_invoice_days'))<span class="text-danger"> {{$errors->first('create_invoice_days')}}</span> @endif</label>
                        <input type="number" name="create_invoice_days" value="{{$data->where('slug','create_invoice_days')->first() ? $data->where('slug','create_invoice_days')->first()->value : ''}}" min="1" step="1" oninput="if (this.value <div 1) this.value = '';" placeholder="Invoice Generation Days EX:30" class="form-control" />
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label ml-1" for="expire_before_msg">send SMS before Expire (Hours) @if($errors->has('expire_before_msg'))<span class="text-danger"> {{$errors->first('expire_before_msg')}}</span> @endif</label>
                        <input type="number" placeholder="EX: 2" name="expire_before_msg" value="{{$data->where('slug','expire_before_msg')->first() ? $data->where('slug','expire_before_msg')->first()->value : ''}}" min='1' class="form-control" id="corn_run_time">
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <h3>General</h3>
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="site_name">site name @if($errors->has('site_name'))<span class="text-danger"> {{$errors->first('site_name')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','site_name')->first() ? $data->where('slug','site_name')->first()->value : ''}}" name="site_name" id="site_name" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_email">company email @if($errors->has('company_email'))<span class="text-danger"> {{$errors->first('company_email')}}</span> @endif</label>
                        <input type="email" value="{{$data->where('slug','company_email')->first() ? $data->where('slug','company_email')->first()->value : ''}}" name="company_email" id="company_email" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_phone">company phone Number @if($errors->has('company_phone'))<span class="text-danger"> {{$errors->first('company_phone')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','company_phone')->first() ? $data->where('slug','company_phone')->first()->value : ''}}" name="company_phone" id="company_phone" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_bkash_number">company Bkash Number @if($errors->has('company_bkash_number'))<span class="text-danger"> {{$errors->first('company_bkash_number')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','company_bkash_number')->first() ? $data->where('slug','company_bkash_number')->first()->value : ''}}" name="company_bkash_number" id="company_bkash_number" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_roket_number">company Roket Number @if($errors->has('company_roket_number'))<span class="text-danger"> {{$errors->first('company_roket_number')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','company_roket_number')->first() ? $data->where('slug','company_roket_number')->first()->value : ''}}" name="company_roket_number" id="company_roket_number" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_nagad_number">company Nogod Number @if($errors->has('company_nagad_number'))<span class="text-danger"> {{$errors->first('company_nagad_number')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','company_nagad_number')->first() ? $data->where('slug','company_nagad_number')->first()->value : ''}}" name="company_nagad_number" id="company_nagad_number" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_help_line">company help line @if($errors->has('company_help_line'))<span class="text-danger"> {{$errors->first('company_help_line')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','company_help_line')->first() ? $data->where('slug','company_help_line')->first()->value : ''}}" name="company_help_line" id="company_help_line" placeholder="" class="form-control" />
                    </div>

                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="form-group d-flex">
                        <div class="w-75">
                            <label class="form-label" for="site_logo">Logo @if($errors->has('site_logo'))<span class="text-danger"> {{$errors->first('site_logo')}}</span> @endif</label>
                            <input type="file" name="site_logo" id="site_logo" placeholder="" class="form-control" />
                            <input type="hidden" name="old_site_logo" value="{{$data->where('slug','company_website')->first() ? $data->where('slug','company_website')->first()->value : ''}}" />
                        </div>
                        <div class="w-25">
                            <img width="60" class="border p-1 float-end" src="{{$data->where('slug','site_logo')->first() ? asset($data->where('slug','site_logo')->first()->value) : ''}}" alt="">
                        </div>
                    </div>
					<div class="form-group d-flex">
                        <div class="w-75">
                            <label class="form-label" for="site_logo">Favicon @if($errors->has('site_logo'))<span class="text-danger"> {{$errors->first('site_logo')}}</span> @endif</label>
                            <input type="file" name="site_logo" id="site_logo" placeholder="" class="form-control" />
                            <input type="hidden" name="old_site_logo" value="{{$data->where('slug','company_website')->first() ? $data->where('slug','company_website')->first()->value : ''}}" />
                        </div>
                        <div class="w-25">
                            <img width="60" class="border p-1 float-end" src="{{$data->where('slug','site_logo')->first() ? asset($data->where('slug','site_logo')->first()->value) : ''}}" alt="">
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label class="form-label" for="company_website">company website @if($errors->has('company_website'))<span class="text-danger"> {{$errors->first('company_website')}}</span> @endif</label>
                        <input type="url" value="{{$data->where('slug','company_website')->first() ? $data->where('slug','company_website')->first()->value : ''}}" name="company_website" id="company_website" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_address">company address @if($errors->has('company_address'))<span class="text-danger"> {{$errors->first('company_address')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','company_address')->first() ? $data->where('slug','company_address')->first()->value : ''}}" name="company_address" id="company_address" placeholder="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="copyright_right">copyright Text @if($errors->has('copyright_right'))<span class="text-danger"> {{$errors->first('copyright_right')}}</span> @endif</label>
                        <input type="text" value="{{$data->where('slug','copyright_right')->first() ? $data->where('slug','copyright_right')->first()->value : ''}}" name="copyright_right" id="copyright_right" placeholder="" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-2"> @can('Settings edit')
                    <input type="submit" value="Save" class="btn btn-outline-primary btn-sm mt-1 float-right">
                    @endcan
                </div>
            </div>
            @can('Settings edit')
        </form>
        @endcan
    </div>
</div>
@endsection