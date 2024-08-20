@extends('layouts/layoutMaster')
@section('title', 'collect-bill')
@section('content')
<div class="card-content p-3 p-md-5">
    <div class="card-body">
        <div class="text-center mb-4">
            <h3 class="mb-2">Add Invoice</h3>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-8 m-auto">
                <form action="{{route('user-store-invoice')}}" class="row g-3" method="POST">
                    @csrf
                    <div class="col-12 text-center mt-2">
                        Package: {{$user->package->name}} <br>
                        <span class="text-success">Expire Date: {{ \Carbon\Carbon::parse($user->expire_date)->format('d-F-Y h:i:s A')}} <br> </span>
                        Bill: {{$user->bill}} <br>
                        Wallet: {{$user->wallet ?? 0}} TK<br>
                        @if($user->discount)
                        User Discount: {{$user->discount}}
                        @endif
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="user_id">User Name</label>
                        <input type="text" readonly value="{{$user->username}}" class="form-control">
                        <input type="text" hidden value="{{$user->id}}" name="user_id" class="form-control">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="invoice_for">Invoice For</label>
                        @if($errors->has('invoice_for')) <br> <span class="text-danger"> {{$errors->first('invoice_for')}}</span> @endif
                        <select id="invoice_for" name="invoice_for" class="form-control" onchange="addBill(this, {{$user->bill}}, {{$user->discount}})">
                            <option value="">Please Select One</option>
                            <option value="monthly_bill">Monthly Bill</option>
                            <option value="customer_add_balance">Add Balance</option>
                            <option value="connection_fee">Connection Fee</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="amount">Total Amount</label>
                        @if($errors->has('amount')) <br> <span class="text-danger"> {{$errors->first('amount')}}</span> @endif
                        <input id="amount" name="amount" class="form-control" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="received_amount">Received Amount</label>
                        @if($errors->has('received_amount')) <br> <span class="text-danger"> {{$errors->first('received_amount')}}</span> @endif
                        <input id="received_amount" name="received_amount" class="form-control" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="paid_by">Paid By</label>
                        @if($errors->has('paid_by')) <br> <span class="text-danger"> {{$errors->first('paid_by')}}</span> @endif
                        <select id="paid_by" name="paid_by" onchange="paidBy(this)" class="form-control">
                            <option value="Bkash">Bkash</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2" id="transaction_id--area">
                        <label class="form-label w-100" id="transaction_id--area-label" for="transaction_id">Transaction Id</label>
                        @if($errors->has('transaction_id')) <br> <span class="text-danger"> {{$errors->first('transaction_id')}}</span> @endif
                        <input id="transaction_id" name="transaction_id" class="form-control" type="text" />
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href="{{ url()->previous() }}" type="reset" class="btn btn-label-secondary btn-reset">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('pricing-script')

<script>
    function addBill(event, price, discount) {
        if (event.value == 'monthly_bill') {
            if (discount != null) {
                document.getElementById('amount').value = (price - discount)
            } else {
                document.getElementById('amount').value = price
            }
            document.getElementById('expire_date').classList.remove('d-none')
        } else if (event.value == 'add_balance') {
            document.getElementById('expire_date').classList.add('d-none')
        }
    }

    function paidBy(event) {
        // if (event.value == 'Cash') {
        //     document.getElementById('transaction_id--area').classList.add('d-none')
        // } else {
        //     document.getElementById('transaction_id--area').classList.remove('d-none')
        // }
    }
</script>
@endpush