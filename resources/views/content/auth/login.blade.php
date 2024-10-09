@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/pages-auth.js')}}"></script>
@endsection

@section('content')

<style>
.custom-login{
	background-color:rgb(44, 175, 254) !important;
	border:none !important;
}
.login{
	color: rgb(44, 175, 254) !important;
}
.custom-login:hover{
	background-color:#ff4c3b !important;
	border:none !important;
}
</style>
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <!-- Login -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="{{url('/')}}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <img src="{{asset(App\Models\AdminSetting::where('slug','site_logo')->first() ? asset(App\Models\AdminSetting::where('slug','site_logo')->first()->value) :'default/isp.jpg')}}" alt="logo">
                            </span>
                            <span style="display:none" class="app-brand-text demo text-body fw-bold ms-1">{{ App\Models\AdminSetting::where('slug','site_name')->first() ? App\Models\AdminSetting::where('slug','site_name')->first()->value : config('variables.templateName')}}</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2 login" style="text-align:center">Login</h4>
                    <h4 class="mb-1 pt-2 d-none">Welcome to {{ App\Models\AdminSetting::where('slug','site_name')->first() ? App\Models\AdminSetting::where('slug','site_name')->first()->value : config('variables.templateName')}}! 👋</h4>
                    <p class="mb-4 d-none">Please sign-in to your account and access the service</p>

                    <form id="formAuthentication" class="mb-3" action="{{route('login.post')}}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="prefix_text" class="form-label">Email or Username</label>
                            @if($errors->has('prefix_text')) <br><small class="text-danger"> {{$errors->first('prefix_text')}}</small> @endif
                            <input type="text" class="form-control" id="prefix_text" name="prefix_text" value="{{old('prefix_text')}}" placeholder="Enter your email or username" autofocus>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-none d-flex justify-content-between">
                                <label class="form-label" for="password">Password</label>
                                <a href="{{url('auth/forgot-password-basic')}}">
                                    <small>Forgot Password?</small>
                                </a>
                            </div>
                            <div class="input-group input-group-merge">
                                @if($errors->has('password'))<span class="text-danger"> {{$errors->first('password')}}</span> <br> @endif
                                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me">
                                <label class="form-check-label" for="remember-me">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary d-grid w-100 custom-login" type="submit">Login</button>
                        </div>
                    </form>

                    <p class="d-none text-center">
                        <span>New on our platform?</span>
                        <a href="{{url('auth/register-basic')}}">
                            <span>Create an account</span>
                        </a>
                    </p>

                    <div class="divider my-4 d-none">
                        <div class="divider-text">Flow Us</div>
                    </div>

                    <div class="d-none d-flex justify-content-center">
                        <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
                            <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
                            <i class="tf-icons fa-brands fa-google fs-5"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-label-twitter">
                            <i class="tf-icons fa-brands fa-twitter fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
</div>
@endsection