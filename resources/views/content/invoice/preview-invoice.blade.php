@extends('layouts/layoutMaster')

@section('title', 'Preview - Invoice')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/app-invoice.css')}}" />
<style>
  .table-responsive {
    min-height: 20px !important;
  }
</style>
@endsection

@section('page-script')
<script src="{{asset('assets/js/offcanvas-add-payment.js')}}"></script>
<script src="{{asset('assets/js/offcanvas-send-invoice.js')}}"></script>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
@endsection

<?php

use App\Models\AdminSetting;

$admindata = new AdminSetting();
$amount_in_words = '';
if(isset($data->amount)){
   $amount_in_words = inwords($data->amount);
}
?>
@section('content')

<div class="row invoice-preview">
  <!-- Invoice -->
  <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-4">
    <div class="card invoice-preview-card" id="invoice-content">
      <div class="card-body">
        <div class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column m-sm-3 m-0">
          <div class="mb-xl-0 mb-4">
            <div class="d-flex svg-illustration mb-4 gap-2 align-items-center">
              @if(!$admindata->where('slug','site_logo')->first() )
              @include('_partials.macros',["height"=>20,"withbg"=>''])
              @else
              <img src="{{asset($admindata->where('slug','site_logo')->first() ) }}" alt class="h-auto rounded-circle">
              @endif
              <span class="app-brand-text fw-bold fs-4">
                {{ $admindata->where('slug','site_name')->first() ? $admindata->where('slug','site_name')->first()->value : config('variables.templateName') }}
              </span>
            </div>
            <p class="mb-2">{{ $admindata->where('slug','company_address')->first() ? $admindata->where('slug','company_address')->first()->value : 'Office 149, 450 South Brand Brooklyn' }}</p>
            <p class="mb-0">{{ $admindata->where('slug','company_email')->first() ? $admindata->where('slug','company_email')->first()->value : 'example@email.com' }}</p>
            <p class="mb-0">{{ $admindata->where('slug','company_phone')->first() ? $admindata->where('slug','company_phone')->first()->value : '8801XXXXXXXXXXXX' }}</p>
          </div>
          <div>
            <h4 class="fw-semibold mb-2">INVOICE: {{$data->invoice_no}}</h4>
            <div class="mb-2 pt-1">
              <span>Date Issues:</span>
              <span class="fw-semibold">{{$data->created_at->format('F d, Y')}}</span>
            </div>
            @if($data->customer)
            <div class="pt-1">
              <span>Expire Date:</span>
              <span class="fw-semibold">{{$data->customer ? $data->customer->expire_date : 'N/A'}}</span>
            </div>
            @endif
          </div>
        </div>
      </div>
      <hr class="my-0" />
      <div class="card-body">
        <div class="row p-sm-3 p-0">
          <div class="col-xl-5 col-md-12 col-sm-5 col-12 mb-xl-0 mb-md-4 mb-sm-0 mb-4">
            <h6 class="mb-3">Invoice To:</h6>
            @if($data->invoice_for == INVOICE_MANAGER_ADD_PANEL_BALANCE)
            <p class="mb-1 text-capitalize">{{$data->franchise_manager->name}}</p>
            <p class="mb-1">{{$data->franchise_manager->address}}</p>
            <p class="mb-1">{{$data->franchise_manager->phone}}</p>
            <p class="mb-1">{{$data->franchise_manager->email}}</p>
            @else
            <p class="mb-1 text-capitalize">{{$data->customer ? $data->customer->full_name : $data->manager->name}}</p>
            <p class="mb-1">{{$data->customer ? $data->customer->address : $data->manager->address}}</p>
            <p class="mb-1">{{$data->customer ? $data->customer->phone : $data->manager->phone}}</p>
            <p class="mb-1">{{$data->customer ? $data->customer->email : $data->manager->email}}</p>
            @endif
            <p class="mb-1">{{$data->comment}}</p>
          </div>
          <div class="col-xl-7 col-md-12 col-sm-7 col-12">
            <h6 style="display:none" class="mb-1">Bill To:</h6>
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
                @if($data->received_amount >0)
                <tr>
                  <td>Paid By</td>
                  <td class="pe-3">:</td>
                  <td>{{$data->paid_by}}</td>
                </tr>
                @endif
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
                <tr>
                  <td>Status</td>
                  <td class="pe-3">:</td>
                  <td class="text-capitalize">{{$data->status}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="table-responsive border-top">
        @if($data->invoice_edit_history->count()>0)
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                Invoice Edit History ({{$data->invoice_edit_history->count()}})
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <table class="table m-0">

                  <head>
                    <tr>
                      <th>SL No.</th>
                      <th>Manager</th>
                      <th>Previous Received Amount</th>
                      <th>Update Amount</th>
                      <th>Total Amount</th>
                      <th>Date</th>
                      <th>Paid By</th>
                    </tr>
                  </head>
                  <tbody>
                    @foreach($data->invoice_edit_history as $index=>$e_item)
                    <tr>
                      <td>{{$index+1}}</td>
                      <td>{{$e_item->manager->name}}</td>
                      <td>{{$e_item->previous_received_amount}}</td>
                      <td>{{$e_item->new_received_amount}}</td>
                      <td>{{$e_item->total_received_amount}}</td>
                      <td>{{$e_item->created_at->format('D-M-Y h:i a')}}</td>
                      <td>{{$e_item->paid_by}}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        @endif
        <table class="table m-0">
          <thead>
            <tr>
              <td colspan="3" class="align-top px-4 py-4">
                <p class="mb-2 mt-3">
                  <span class="ms-3 fw-semibold">Salesperson:</span>
                  <span class="text-capitalize">{{$data->manager ? $data->manager->name :'N/A'}}</span>
                </p>
                <span class="ms-3">Thanks for your business</span>
              </td>
              <td class="text-end pe-3 py-4">
                <p class="mb-2 pt-3">Subtotal:</p>
                <p class="mb-2">Due:</p>
                <p class="mb-0 pb-3">Total:</p>
              </td>
              <td class="ps-2 py-4">
                <p class="fw-semibold mb-2 pt-3">{{$data->amount}} TK</p>
                <p class="fw-semibold mb-2 b">{{$data->due_amount}} TK</p>
                <p class="fw-semibold mb-0 pb-3">{{$data->amount}} TK</p>
              </td>
            </tr>
            </tbody>
        </table>
      </div>

      <div class="card-body mx-3">
          <div class="row">
			<div class="col-8">
			  <span class="fw-bold">Amount in words:</span>
			  <span>{{ $amount_in_words }}</span>
			</div>
			<div class="col-4">
			  <span class="fw-bold">Invoice issued:</span>
			  <span>{{auth()->user()->name}}</span>
			</div>
		  </div>
      </div>
    </div>
  </div>
  <!-- /Invoice -->
  <!-- Invoice Actions -->
  <div class="col-xl-3 col-md-4 col-12 invoice-actions">
    <div class="card">
      <div class="card-body">
        @can('Invoice Print')
        <a class="btn btn-primary d-grid w-100 custom-login waves-effect waves-light" onclick="printInvoice()" href="javascript:void(0)">
          Print
        </a>
        @endcan
      </div>
    </div>
  </div>
  <!-- /Invoice Actions -->
</div>
<!-- Offcanvas -->
@include('_partials/_offcanvas/offcanvas-send-invoice')
@include('_partials/_offcanvas/offcanvas-add-payment')
<!-- /Offcanvas -->
@endsection
<script>
    function printInvoice(){
		
	  var body = document.getElementById('body').innerHTML;
	  var invoiceData = document.getElementById('invoice-content').innerHTML;
	  document.getElementById('body').innerHTML = invoiceData;
      window.print();
	  document.getElementById('body').innerHTML = body;
    }
</script>