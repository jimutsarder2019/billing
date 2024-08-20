@extends('layouts/layoutMaster')
@section('title','new Invoice')


@section('content')
<div class="row">
    <div class="col-sm-12 col-md-8 m-auto">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h3 class="mb-2">New Invoice</h3>
                </div>
                <form action="{{route('user-store-invoice')}}" class="row g-3" method="POST">
                    @csrf
                    <div class="col-12 mt-2">
                        <label class="form-label" for="user_id">User Name <i title="Monthly Bill After Discount" style="font-size: 15px;" class="menu-icon bi bi-info-circle"></i></label>
                        @if($errors->has('user_id'))<span class="text-danger"> {{$errors->first('user_id')}}</span> <br>@endif
                        <select name="user_id" id="customer_user_id" class="select2 form-select">
                            <option value="">Please Select One</option>
                            @foreach($users as $u)
                            <option value="{{$u->id}}|{{$u->bill-$u->discount}}">{{$u->username}} | Monthly BIll:{{$u->bill-$u->discount}}TK</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="invoice_for">Invoice For</label>
                        @if($errors->has('invoice_for'))<span class="text-danger"> {{$errors->first('invoice_for')}}</span> <br>@endif
                        <select id="invoice_for" name="invoice_for" class="form-control" onchange="addBill(this)">
                            <option value="">Please Select One</option>
                            <option value="monthly_bill">Monthly Bill</option>
                            <option value="customer_add_balance">Customer Add Balance</option>
                            <option value="connection_fee">Connection Fee</option>
                        </select>
                    </div>
                    <!-- <div class="col-12 mt-2">
                        <label class="form-label w-100" for="expire_date">Expire Date</label>
                        <input id="expire_date" name="expire_date" class="form-control" type="datetime-local" />
                    </div> -->
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="amount">Total Amount</label>
                        @if($errors->has('amount'))<span class="text-danger"> {{$errors->first('amount')}}</span> <br>@endif
                        <input id="amount" name="amount" class="form-control set_props_el" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="received_amount">Received Amount</label>
                        @if($errors->has('received_amount'))<span class="text-danger"> {{$errors->first('received_amount')}}</span> <br>@endif
                        <input id="received_amount" name="received_amount" class="form-control set_props_el" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="searchable-select">Paid By</label>
                        @if($errors->has('paid_by'))<span class="text-danger"> {{$errors->first('paid_by')}}</span> <br>@endif
                        <select id="select_paid_by" name="paid_by" class="select2 form-select" onchange="changePaidBy()">
                            <option value="Cash">Cash</option>
                            <option value="Bkash">Bkash</option>
                        </select>
                    </div>
                    <div id="trx_id_area" class="col-12 mt-2 d-none">
                        <label class="form-label w-100" for="transaction_id">Transaction Id</label>
                        @if($errors->has('transaction_id'))<span class="text-danger"> {{$errors->first('transaction_id')}}</span> <br>@endif
                        <input id="transaction_id" name="transaction_id" class="form-control" type="text" />
                    </div>
                    <div id="trx_note_area" class="col-12 mt-2">
                        <label class="form-label w-100" for="note">Note</label>
                        @if($errors->has('transaction_id'))<span class="text-danger"> {{$errors->first('transaction_id')}}</span> <br>@endif
                        <input id="note" name="transaction_id" class="form-control" placeholder="Note" type="text" />
                    </div>
                    <div class="form-group w-25">
                        <label class="form-label w-100" for="amount">send sms</label>
                        <input type="checkbox" class="form-check-input" name="is_send_sms" value="1">
                    </div>
                    <div class="col-12 text-center mt-4">
                        @include('components/submit-confirm-model')
                        <button data-bs-toggle="modal" data-bs-target="#submitConfirmModal" data-bs-dismiss="modal" type="button" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href="{{route('invoice.index')}}" type="reset" class="btn btn-label-secondary btn-reset">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    // change monthly bill
    function addBill(event) {
        if (event.value == 'monthly_bill') {
            let user_data = $('#customer_user_id').val();
            const user_data_array = user_data.split('|')
            console.log(user_data_array[1]);
            document.getElementById('received_amount').value = user_data_array[1];
            document.getElementById('amount').value = user_data_array[1];
            $(".set_props_el").attr("readonly", "readonly");

        } else {
            document.getElementById('received_amount').value = ''
            document.getElementById('amount').value = ''
            $(".set_props_el").removeAttr("readonly");

        }
    }


    // change paid by 
    function changePaidBy() {
        const paid_by = document.getElementById('select_paid_by').value
        if (paid_by == 'Cash') {
            $('#trx_id_area').addClass('d-none')
            $('#trx_note_area').removeClass('d-none')
        } else {
            $('#trx_id_area').removeClass('d-none')
            $('#trx_note_area').addClass('d-none')
        }
    }
</script>