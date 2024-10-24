<?php
use App\Models\AdminSetting;

$settings =  AdminSetting::select('slug', 'value')->get();
?>
<!DOCTYPE html>

<html lang="{{ session()->get('locale') ?? app()->getLocale() }}" class="{{ $configData['style'] }}-style {{ $navbarFixed ?? '' }} {{ $menuFixed ?? '' }} {{ $menuCollapsed ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }}" dir="{{ $configData['textDirection'] }}" data-theme="{{ $configData['theme'] }}" data-assets-path="{{ asset('/assets') . '/' }}" data-base-url="{{url('/')}}" data-framework="laravel" data-template="{{ $configData['layout'] . '-menu-' . $configData['theme'] . '-' . $configData['style'] }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title') | {{App\Models\AdminSetting::where('slug','site_name')->first() ? App\Models\AdminSetting::where('slug','site_name')->first()->value : config('variables.templateName')}}
  </title>
  <meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app_url" content="{{config('app.url')}}">
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{$settings->where('slug','site_favicon')->first() ? asset($settings->where('slug','site_favicon')->first()->value) : asset('assets/img/favicon/favicon.ico')}}" />
  @notifyCss
  <!-- Include Styles -->
  @include('layouts/sections/styles')
  <!-- Include Scripts for customizer, helper, analytics, config -->
  @include('layouts/sections/scriptsIncludes')
</head>

<body id="body">
  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->
  <!-- Include Scripts -->
  @include('components/delete-model')
  @include('layouts/sections/scripts')
  <x-notify::notify />
  @notifyJs
  <script type="text/javascript">
    function openConfirmation(url, method = '') {
      // Log the method parameter for debugging purposes
      console.log(method);

      // Select the form element with ID "delete_form"
      var selectForm = document.querySelector('#delete_form');

      selectForm.setAttribute("method", method !== '' ? method : 'POST');

      // Set the action attribute of the form
      selectForm.setAttribute("action", url);

      // Show the modal with the ID "deleteConfirmModal"
      $("#deleteConfirmModal").modal('show');
    }
  </script>
</body>

</html>