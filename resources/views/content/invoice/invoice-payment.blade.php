@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title','Invoice Payment')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="text-center mb-4">
                    <h3 class="mb-2">Invoice Payment</h3>
                    <div class="row">
                        <div class="col-8 m-auto">
                            <div class="alert alert-success">
                                <h6 class="mb-2 text-success">User Name: {{$data->customer ? $data->customer->username : $data->manager->name}}</h6>
                                @if($data->customer) <h6 class="mb-2 text-warning">Expire Date {{ \Carbon\Carbon::parse($data->customer->expire_date)->format('Y-m-d h:i:s A')}} </h6> @endif
                                @if($data->due_amount) <h6 class="mb-2 text-success">Due: {{$data->due_amount}} TK </h6> @endif
                            </div>
                        </div>
                    </div>
                </div>
                <form action="{{route('invoice_payment', $data->id)}}" class="row g-3" method="POST">
                    @method('put')
                    @csrf
                    <div class="col-12 mt-2">
                        <label class="form-label" for="invoice_for">Invoice For</label>
                        <input type="text" name="" value="{{$data->invoice_for}}" disabled class="form-control" id="">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="amount">Total Amount</label>
                        <input id="amount" name="amount" value="{{$data->amount}}" readonly class="form-control" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="received_amount">{{$data->status == STATUS_DUE ? 'Paid' :'Received'}} Amount</label>
                        <input id="received_amount" required name="received_amount" value="{{$data->status == STATUS_DUE ? $data->received_amount : $data->amount}}" min="{{$data->received_amount}}" readonly type="number" class="form-control" />
                    </div>
                    @if($data->status == STATUS_DUE)
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="payable_amount">Payable Amount</label>
                        <input id="due_amount" required name="payable_amount" value="{{$data->due_amount}}" max="{{$data->due_amount}}" class="form-control" type="number" />
                    </div>
                    @endif
                    <div class="col-12 mt-2">
                        <label class="form-label" for="searchable-select">Paid By</label>
                        <select id="searchable" required name="paid_by" class="form-control" onchange="change_paid_by(event)">
                            <option>Select</option>
                            @foreach(PAYMENT_METHOD_ITEMS as $p_item)
                            <option {{isset($data) && $data->paid_by == $p_item ? 'selected':''}} value="{{$p_item}}">{{$p_item}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="trx_area" class="col-12 mt-2">
                        <label class="form-label w-100" id="transection_id_label" for="transaction_id">Transaction Id</label>
                        <input id="transaction_id" name="transaction_id" value="{{$data->transaction_id}}" class="form-control" type="text" />
                    </div>
                    <div class="col-12 text-center mt-4">
                        @include('components/submit-confirm-model',['message'=>"Next Expire Date ".\Carbon\Carbon::parse($data->customer->expire_date)->addmonth(), 'message_text_color'=>'danger'])
                        <button data-bs-toggle="modal" data-bs-target="#submitConfirmModal" data-bs-dismiss="modal" type="button" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href="{{url()->previous()}}" class="btn btn-label-secondary btn-reset">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function change_paid_by(event) {
        if (event.target.value == 'Cash') {
            $('#transection_id_label').html('Note')
        } else {
            $('#transection_id_label').html('Transaction Id')
        }
    }
</script>
@endsection