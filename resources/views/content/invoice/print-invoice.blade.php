@extends('layouts/layoutMaster')

@section('title', 'Invoice Print')

@section('page-style')
<!-- <link rel="stylesheet" href="{{asset('assets/vendor/css/pages/app-invoice-print.css')}}" /> -->
<style>
  .table-responsive {
    min-height: 20px !important;
  }
</style>
</style>
@endsection

@section('page-script')
<script src="{{asset('assets/js/app-invoice-print.js')}}"></script>
@endsection
<style>
  @media print {
    .inv_no {
      font-size: 15px;
    }

    .w-40 {
      width: 40% !important;
    }

    .w-60 {
      width: 60% !important;
    }

    #layout-navbar,
    footer {
      display: none;
    }

    .template-customizer-open-btn {
      display: none !important;
    }

    .p-table-style {
      min-height: 0px !important;
    }
  }
</style>
<?php

use App\Models\AdminSetting;

$admindata = new AdminSetting()
?>
@section('content')
<div class="invoice-print p-5">
  <div class="d-flex justify-content-between flex-row">
    <div class="mb-4">
      <div class="d-flex svg-illustration mb-3 gap-2">
        @if(!$admindata->where('slug','site_logo')->first() )
        @include('_partials.macros',["height"=>20,"withbg"=>''])
        @else
        <img src="{{asset($admindata->where('slug','site_logo')->first() ) }}" alt class="h-auto rounded-circle">
        @endif
        <span class="app-brand-text fw-bold">
          {{ $find = $admindata->where('slug','site_name')->first() ? $admindata->where('slug','site_name')->first()->value : config('variables.templateName') }}
        </span>
      </div>
      <p class="mb-2">{{ $find = $admindata->where('slug','company_address')->first() ? $admindata->where('slug','company_address')->first()->value : 'Office 149, 450 South Brand Brooklyn' }}</p>
      <p class="mb-0">{{ $find = $admindata->where('slug','company_email')->first() ? $admindata->where('slug','company_email')->first()->value : 'example@email.com' }}</p>
      <p class="mb-0">{{ $find = $admindata->where('slug','company_phone')->first() ? $admindata->where('slug','company_phone')->first()->value : '8801XXXXXXXXXXXX' }}</p>
    </div>
    <div>
      <h4 class="fw-bold inv_no">INVOICE: {{$data->invoice_no}}</h4>
      <div class="mb-2">
        <span class="text-muted">Date Issues:</span>
        <span class="fw-bold">{{$data->created_at->format('F d, Y')}}</span>
      </div>
    </div>
  </div>

  <hr />

  <div class="row mb-4 d-flex">
    <div class="col-sm-5 w-40">
      <h6>Invoice To:</h6>
      <p class="mb-1">{{$data->customer ? $data->customer->full_name : $data->manager->name}}</p>
      <p class="mb-1">{{$data->customer ? $data->customer->address : $data->manager->address}}</p>
      <p class="mb-1">{{$data->customer ? $data->customer->phone : $data->manager->phone}}</p>
      <p class="mb-0">{{$data->customer ? $data->customer->email : $data->manager->email}}</p>
    </div>
    <div class="col-sm-7 w-60">
      <h6>Bill To:</h6>
      <table>
        <tbody>
          <tr>
            <td>Payable Amount</td>
            <td class="pe-3">:</td>
            <td><strong>{{$data->amount}} TK</strong></td>
          </tr>
          <tr>
            <td>Received Amount</td>
            <td class="pe-3">:</td>
            <td><strong>{{$data->received_amount}} TK</strong></td>
          </tr>
          <tr>
            <td>Transefer by</td>
            <td class="pe-3">:</td>
            @if($data->paid_by == 'bkash')
            <td>{{$data->paid_by}}</td>
            @else
            <td>Cash</td>
            @endif
          </tr>
          @if($data->transection_id)
          <tr>
            <td>{{$data->paid_by == 'Cash' ? 'Note':'Transection Id'}}</td>
            <td class="pe-3">:</td>
            <td>{{$data->transection_id}}</td>
          </tr>
          @endif
          <tr>
            <td>Invoice For</td>
            <td class="pe-3">:</td>
            <td class="text-capitalize">{{str_replace('_',' ',$data->invoice_for)}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="table-responsive p-table-style">
    <table class="table m-0">

      <tbody>
        <tr>
          <td colspan="3" class="align-top p-0 py-3">
            <p class="mb-2">
              <span class="me-1 fw-bold">Salesperson:</span>
              <span class="text-capitalize">{{auth()->user()->name}}</span>
            </p>
            <span>Thanks for your business</span>
          </td>
          <td class="text-end pe-3 py-4">
            <p class="mb-2 pt-3">Subtotal:</p>
            <p class="mb-2">Due:</p>
            <p class="mb-0 pb-3">Total:</p>
          </td>
          <td class="ps-2 py-4">
            <p class="fw-semibold mb-2 pt-3">{{$data->amount}} TK</p>
            <p class="fw-semibold mb-2 b">{{$data->due_amount}} TK</p>
            <p class="fw-semibold mb-0 pb-3">{{$data->amount}}</p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="row">
    <div class="col-12">
      <span class="fw-bold">Note:</span>
      <span>Billing System Made By <a href="www.smartisp.net" target="_blank" rel="noopener noreferrer">www.smartisp.net</a></span>
    </div>
  </div>
</div>
@endsection