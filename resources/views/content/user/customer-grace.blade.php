@extends('layouts/layoutMaster')
@section('title') Add Invoice @endsection
@section('content')
<div class="card-content p-3 p-md-5">
    <div class="card-body">
        <div class="text-center mb-4">
            <h3 class="mb-2">Allow Grace</h3>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-8 m-auto">
                <form action="{{route('customer_allow_grace', $user->id)}}" class="row g-3" method="POST">
                    @method('put')
                    @csrf
                    <div class="col-12 text-center mt-2">
                        <p class="badge text-bg-primary">Total Grace Limit: {{$grace ?? 0}} Days</p>
                        @if($user->allow_grace)
                        <br>
                        <p class="badge text-bg-warning">Already Allow Grace {{$user->allow_grace}} Days</p>
                        @endif
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="user_id">User Name</label>
                        <input type="text" disabled value="{{$user->username}}" class="form-control">
                    </div>
                    <div class="col-12 mt-2" id="transaction_id--area">
                        <label class="form-label w-100" for="allow_grace">Allow Grace <small>(Day)</small></label>
                        @if($errors->has('allow_grace')) <br> <span class="text-danger"> {{$errors->first('allow_grace')}}</span> @endif
                        <input id="allow_grace" name="allow_grace" placeholder="EX:1" class="form-control" min="1" max="{{$grace}}" type="number" required />
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
        if (event.value == 'Cash') {
            document.getElementById('transaction_id--area').classList.add('d-none')
        } else {
            document.getElementById('transaction_id--area').classList.remove('d-none')

        }
    }
</script>
@endpush