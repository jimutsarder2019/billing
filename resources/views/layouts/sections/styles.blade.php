<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet">

<link rel="stylesheet" href="{{ asset(mix('assets/vendor/fonts/fontawesome.css')) }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/fonts/tabler-icons.css')) }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/fonts/flag-icons.css')) }}" />

<!-- Core CSS -->
<link rel="stylesheet"
    href="{{ asset(mix('assets/vendor/css' . $configData['rtlSupport'] . '/core' . ($configData['style'] !== 'light' ? '-' . $configData['style'] : '') . '.css')) }}"
    class="{{ $configData['hasCustomizer'] ? 'template-customizer-core-css' : '' }}" />
<link rel="stylesheet"
    href="{{ asset(mix('assets/vendor/css' . $configData['rtlSupport'] . '/' . $configData['theme'] . ($configData['style'] !== 'light' ? '-' . $configData['style'] : '') . '.css')) }}"
    class="{{ $configData['hasCustomizer'] ? 'template-customizer-theme-css' : '' }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/css/demo.css')) }}" />
<!-- <link rel="stylesheet" href="{{ asset('assets/plugins/') }}/bootstrap-icons.css" /> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />

<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')) }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/node-waves/node-waves.css')) }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/typeahead-js/typeahead.css')) }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />

<style>
    #laravel-notify,
    .notify {
        z-index: 99999999 !important;
    }

    label {
        text-transform: capitalize !important;
    }

    /* ====================data table paginateion================= */
    .data_table_pagination svg {
        width: 25px !important;
    }

    .table-responsive {
        min-height: 150px !important;
    }

    .dataTables_info,
    .dataTables_paginate,
    .data_table_pagination nav .flex.justify-between.flex-1.sm\:hidden {
        display: none;
    }

    .data_table_pagination nav a,
    .data_table_pagination svg {
        margin-left: -1px;
    }

    /* .data_table_pagination nav .cursor-default {
        background-color: #242c6d !important;
        border-color: #242c6d !important;
        color: #ffffff;
        margin-left: -1px;
    } */
    span[aria-current="page"] .cursor-default {
        background-color: #685dd8 !important;
        color: #fff !important;
        margin-left: -1px;
    }

    /* .data_table_pagination nav .cursor-default {
        background-color: #685dd8 !important;
        color: #fff !important;
    } */

    .w-10 {
        width: 10% !important;
    }

    .w-20 {
        width: 20% !important;
    }

    .w-90 {
        width: 90% !important;
    }

    .form-control::placeholder {
        text-transform: capitalize;
    }

    @media (min-width: 1400px) {
        .container-xxl {
            max-width: none !important;
        }
    }

    .font_16 {
        font-size: 16px !important
    }
</style>
<!-- Vendor Styles -->
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')
