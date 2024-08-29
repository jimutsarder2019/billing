@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title','Invoice Payment')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12 col-md-8 m-auto">
                @php
                $invoices = App\Models\Invoice::select('id', 'invoice_no','status','amount')->latest()->get();
                @endphp
                <form action="{{route('showInvoice')}}" method="get">
                    <select name="inv_id" id="" class="select select2" onchange="this.form.submit()">
                        @foreach($invoices as $inv)
                        <option {{$data->id == $inv->id ? 'selected':''}} value="{{$inv->id}}">{{$inv->invoice_no}} | {{$inv->amount}} TK | {{$inv->status}}</option>
                        @endforeach
                    </select>
                </form>
                <div class="text-center mb-4">
                    <h3 class="mb-1">Edit Invoice</h3>
                    <h4>User Name: {{$data->customer ? $data->customer->username :'N/A'}}</h4>
                </div>
                <form action="{{route('invoice.update', $data->id)}}" class="row g-3" method="POST">
                    @method('put')
                    @csrf
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="Inv_no">Invoice No</label>
                        <input value="{{$data->invoice_no}}" readonly class="form-control" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="amount">Total Amount</label>
                        <input id="amount" name="amount" value="{{$data->amount}}" class="form-control" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="received_amount">{{$data->status == STATUS_DUE ? 'Paid' :'Received'}} Amount</label>
                        <input id="received_amount" name="received_amount" value="{{$data->received_amount}}" {{$data->status == STATUS_DUE ? 'readonly' : ''}} class="form-control" type="number" />
                    </div>
                    @if($data->status == STATUS_DUE)
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="payable_amount">Payable Amount</label>
                        <input id="due_amount" name="payable_amount" value="{{$data->due_amount}}" class="form-control" type="number" />
                    </div>
                    @endif
                    <div class="col-12 mt-2">
                        <label class="form-label" for="searchable-select">Paid By</label>
                        <select id="searchable-select" required name="paid_by" class="select2 form-select">
                            <option {{$data->paid_by == 'paid_by' ? 'selected':''}} value="Bkash">Bkash</option>
                            <option {{$data->paid_by == 'paid_by' ? 'selected':''}} value="Cash">Cash</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="searchable-select">Status</label>
                        <select id="searchable-select" required name="status" class="form-control">
                            <option {{$data->status == STATUS_PAID ? 'selected':''}} value="{{STATUS_PAID}}">{{STATUS_PAID}}</option>
                            <option {{$data->status == STATUS_DUE ? 'selected':''}} value="{{STATUS_DUE}}">{{STATUS_DUE}}</option>
                            <option {{$data->status == STATUS_OVER_PAID ? 'selected':''}} value="{{STATUS_OVER_PAID}}">{{STATUS_OVER_PAID}}</option>
                            <option {{$data->status == STATUS_PENDING ? 'selected':''}} value="{{STATUS_PENDING}}">{{STATUS_PENDING}}</option>
                            <option {{$data->status == STATUS_ACCEPTED ? 'selected':''}} value="{{STATUS_ACCEPTED}}">{{STATUS_ACCEPTED}}</option>
                            <option {{$data->status == STATUS_REJECTED ? 'selected':''}} value="{{STATUS_REJECTED}}">{{STATUS_REJECTED}}</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="transaction_id">Transaction Id</label>
                        <input id="transaction_id" name="transaction_id" value="{{$data->transaction_id}}" class="form-control" type="text" />
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button onclick="return confirm('Are you sure to submit')" type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href="{{url()->previous()}}" class="btn btn-label-secondary btn-reset">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection