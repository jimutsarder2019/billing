@extends('layouts/layoutMaster')
@section('title', 'Change Customer expire date')
@section('content')
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-12 col-md-6 m-auto">
        <div class="text-center mb-4">
          <h3 class="mb-2">Change Customer Expire date</h3>
        </div>
        <form action="{{route('user-change-expire-date-put', $user->id)}}" class="row g-3" method="POST">
          @method('put')
          @csrf
          <div class="col-12 text-center mt-2">
            Expire Data {{$user->expire_date}}
          </div>
          <div class="col-12 text-center mt-2">
            Username: {{$user->username}}
          </div>
          @if($user->package)
          <div class="col-12 text-center mt-2">
            Package: {{$user->package->name}}<br>
            Bill: {{$user->bill}} TK<br>
            @if($user->discount)
            User Discount: {{$user->discount}} TK
            @endif
            <input type="hidden" id="user_bill" value="{{$user->bill}}">
            <input type="hidden" id="user_discount" value="{{$user->discount}}">
            <input type="hidden" id="current_expire_date" value="{{$user->expire_date}}">
            <input type="hidden" name="package" value="{{$user->purchase_package->name}}|{{$user->purchase_package->name}}">
          </div>
          @endif
          @if(auth()->user()->type == 'dfgffgd')
          <div class="d-flex">
            <div class="">
              <input type="radio" class="form-check-input" checked onchange="change_resons('adjust_date')" name="select_custom_option_type" id="select_custom_option_type_duration_duration" value="duration">
            </div>
            <div class="">
              <label class="form-label w-100" for="select_custom_option_type_duration_duration">Adjust date</label>
            </div>
            <div class="ms-2">
              <input type="radio" class="form-check-input" onchange="change_resons('customer_request')" name="select_custom_option_type" id="select_custom_option_type_duration_date" value="date">
            </div>
            <div class="">
              <label class="form-label float-start w-100" for="select_custom_option_type_duration_date">Customer Request</label>
            </div>
          </div>
          @endif
          @can('Custom Payments')
          @if($errors->has('custom_expire_date'))<span class="text-danger"> {{$errors->first('custom_expire_date')}}</span> @endif
          <div class="col-12 mt-2 d-flex">
            <label class="form-label" for="expire_date">Custom Expire Date</label>
            <input onchange="changeCustomerExpireDate()" type="datetime-local" class="form-control" id="custom_expire_date" name="custom_expire_date" placeholder="Expire Date" />
          </div>
          @endcan
          <div class="" id="paid_by-area">
            <div class="col-12 mt-2">
              <label class="form-label w-100" for="amount">Total Amount</label>
              @if($errors->has('amount'))<span class="text-danger"> {{$errors->first('amount')}}</span> @endif
              <input id="amount" name="amount" class="form-control" type="number" value="{{old('aamount')}}" readonly />
            </div>
            <div class="col-12 mt-2">
              <label class="form-label" for="paid_by">Paid By</label>
              @if($errors->has('paid_by'))<span class="text-danger"> {{$errors->first('paid_by')}}</span> @endif
              <select id="paid_by" name="paid_by" class="form-control" onchange="toggleTransactionIdField(event)">
                <option value="Cash">Cash</option>
                <option value="Bkash">Bkash</option>
              </select>
            </div>
            <div class="col-12 mt-2 d-none" id="trx_cp">
              <label class="form-label w-100" for="transaction_id">Transaction Id</label>
              @if($errors->has('transaction_id'))<span class="text-danger"> {{$errors->first('transaction_id')}}</span> @endif
              <input id="transaction_id" name="transaction_id" class="form-control" type="text" />
            </div>
            <div class="form-group d-flex mt-3">
              <input type="checkbox" class="form-check-input" id="is_send_welcome_sms" name="is_send_welcome_sms" value="1">
              <label class="form-label w-100" for="is_send_welcome_sms">Send SMS</label>
            </div>
          </div>
          <div class="col-12 text-center mt-4">
            <button onclick="return confirm('are you sure to submit')" type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <a href="{{ url()->previous() }}" class="btn btn-label-secondary btn-reset">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

<script>
  function change_is_custom_payment() {
    let is_custom_payment = $("#is_custom_payment")
    if (is_custom_payment.prop("checked")) {
      $('#select_duration_area').removeClass('d-none')
      document.getElementById('custom_payment_duration').value = ''
    } else {
      $('#select_duration_area').addClass('d-none')
      let user_bill = $("#user_bill").val()
      let user_discount = $("#user_discount").val()
      let p_d = $("#custom_payment_duration").val()
      const cal = user_bill - user_discount
      document.getElementById('amount').value = cal
      document.getElementById('received_amount').value = cal
    }
  }

  // selectCustom_option
  function selectCustom_option(val) {
    if (val == 'duration') {
      $('#custom_payment_duration').removeClass('d-none')
      $('#custom_payment_date').addClass('d-none')
      document.getElementById('custom_expire_date').value = ''
    } else {
      $('#custom_payment_date').removeClass('d-none')
      $('#custom_payment_duration').addClass('d-none')
      document.getElementById("custom_payment_duration").value = ''

    }
  }

  // changeCustomerExpireDate
  function changeCustomerExpireDate() {
    let current_expire_date = document.getElementById('current_expire_date').value
    let expire_date = document.getElementById('custom_expire_date').value
    // Current date
    var today = new Date();
    // Target date
    var present_expire_date = new Date(current_expire_date);
    var targetDate = new Date(expire_date);
    // Calculate difference in milliseconds
    var today_diff = (targetDate - today);
    var diff = (targetDate - present_expire_date);
    // Convert to days
    var daysDiff = Math.floor(diff / (1000 * 60 * 60 * 24));
    if (today_diff <= 0) {
      alert('date must be gretter then today')
      document.getElementById('custom_expire_date').value = ''
      return
    }
    var year = today.getFullYear();
    var month = today.getMonth();
    // Note: Months are zero-based in JavaScript (0 = January, 1 = February, ..., 11 = December)
    var lastDay = new Date(year, month + 1, 0).getDate();
    let user_bill = $("#user_bill").val()
    let user_discount = $("#user_discount").val()
    let per_day_bill = (user_bill / lastDay).toFixed(2);
    console.log('per_day_bill', per_day_bill);
    console.log(daysDiff);
    let par_day_discount = (user_discount / lastDay).toFixed(2);
    let cal = 0
    if (daysDiff > 29) {
      cal = (per_day_bill * daysDiff) - (par_day_discount * daysDiff)
    } else {
      cal = (per_day_bill * daysDiff)

    }

    document.getElementById('amount').value = cal.toFixed(2)
  }

  // change duration
  function change_resons(val) {
    if (val == 'adjust_date') {
      $('#paid_by-area').addClass('d-none')
    } else {
      $('#paid_by-area').removeClass('d-none')
    }
    // let user_bill = $("#user_bill").val()
    // let user_discount = $("#user_discount").val()
    // let p_d = $("#custom_payment_duration").val()
    // const cal = user_bill * p_d - user_discount * p_d
    // document.getElementById('amount').value = cal
    // document.getElementById('received_amount').value = cal
  }

  // change duration
  function toggleTransactionIdField(e) {
    if (e.target.value == 'Cash') {
      $('#trx_cp').addClass('d-none')
    } else {
      $('#trx_cp').removeClass('d-none')
    }
  }
</script>