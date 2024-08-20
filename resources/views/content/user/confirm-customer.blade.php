@extends('layouts/layoutMaster')
@section('title', 'confirm Payment')
@section('content')
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-12 col-md-6 m-auto">
        <div class="text-center mb-4">
          <h3 class="mb-2">Confirm Payment</h3>
        </div>
        <form action="{{route('user-approve-customer', $user->id)}}" class="row g-3" method="POST">
          @csrf
          <div class="col-12 text-center mt-2">
            Username: {{$user->username}}
          </div>
          @if($user->package)
          <div class="col-12 text-center mt-2">
            Package: {{$user->package->name}}<br>
            Bill: {{$user->bill}} TK<br>
            User Discount: {{$user->discount}} TK
            <input type="hidden" id="user_bill" value="{{$user->bill}}">
            <input type="hidden" id="user_discount" value="{{$user->discount}}">
          </div>
          @endif
          @can('Custom Payments')
          <div class="col-12 mt-2 d-flex">
            <div class="form-group w-25">
              <label class="form-label w-100" for="is_custom_payment">Custom Payment</label>
              <input type="checkbox" class="form-check-input" onchange="change_is_custom_payment()" id="is_custom_payment" name="is_custom_payment" value="1">
            </div>
            <div id="select_duration_area" class="form-group w-75 d-none">
              <div class="d-flex">
                <div class="">
                  <input type="radio" class="form-check-input" onchange="selectCustom_option('duration')" name="select_custom_option_type" id="select_custom_option_type_duration_duration" value="duration">
                </div>
                <div class="">
                  <label class="form-label w-100" for="select_custom_option_type_duration_duration">Select Duration</label>
                </div>
                <div class="ms-2">
                  <input type="radio" class="form-check-input" onchange="selectCustom_option('date')" name="select_custom_option_type" id="select_custom_option_type_duration_date" value="date">
                </div>
                <div class="">
                  <label class="form-label float-start w-100" for="select_custom_option_type_duration_date">Select Date</label>
                </div>
              </div>
              <select id="custom_payment_duration" name="custom_payment_duration" class="d-none form-control mt-2" onchange="change_duration()">
                <option value="">Select</option>
                @for($i = 1; $i < 12; $i++) <option value="{{ $i }}">{{ $i }} Months</option>
                  @endfor
                  <option value="12">1 Year</option>
              </select>
              <div class="d-none" id="custom_payment_date">
                @if($errors->has('expire_date'))<span class="text-danger"> {{$errors->first('expire_date')}}</span> @endif
                <label class="form-label" for="expire_date">Custom Expire Date</label>
                <input onchange="changeCustomerExpireDate()" type="datetime-local" class="form-control" id="custom_expire_date" name="custom_expire_date" placeholder="Expire Date" />
              </div>
            </div>
          </div>
          @endcan
          <div class="col-12 mt-2">
            <label class="form-label w-100" for="amount">Total Amount</label>
            @if($errors->has('amount'))<span class="text-danger"> {{$errors->first('amount')}}</span> @endif
            <input id="amount" name="amount" class="form-control" type="text" @if($user->discount != null) value="{{$user->bill - $user->discount}}" @else value="{{$user->bill}}" @endif readonly />
          </div>
          <div class="col-12 mt-2">
            <label class="form-label w-100" for="received_amount">Received Amount</label>
            @if($errors->has('received_amount'))<span class="text-danger"> {{$errors->first('received_amount')}}</span> @endif
            <input id="received_amount" name="received_amount" class="form-control" type="number" value="{{$user->bill - $user->discount}}" readonly @if($user->discount != null) min="{{$user->bill - $user->discount}}" @else min="{{$user->bill}}" @endif />
          </div>
          <div class="col-12 mt-2">
            <label class="form-label" for="paid_by">Paid By</label>
            <select id="paid_by" name="paid_by" class="form-control" onchange="toggleTransactionIdField(event)">
              <option value="Cash">Cash</option>
              <option value="Bkash">Bkash</option>
            </select>
          </div>
          <div class="col-12 mt-2" id="trx_cp">
            <label class="form-label w-100" for="transaction_id" id="transaction_id_label">Note</label>
            @if($errors->has('transaction_id'))<span class="text-danger"> {{$errors->first('transaction_id')}}</span> @endif
            <input id="transaction_id" name="transaction_id" class="form-control" type="text" />
          </div>
          <div class="col-12 mt-2">
            <label for="">Send Sms</label>
            <div class="d-flex">
              <div class="form-group d-flex">
                <input type="checkbox" class="form-check-input" id="is_send_welcome_sms" name="is_send_welcome_sms" checked value="1">
                <label class="form-label w-100" for="is_send_welcome_sms">Welcome SMS</label>
              </div>
              <div class="form-group d-flex ms-3">
                <input type="checkbox" class="form-check-input" id="is_send_new_account_sms" name="is_send_new_account_sms" value="1">
                <label class="form-label w-100" for="is_send_new_account_sms">New Account Details</label>
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-4">
            <button onclick="return confirm('are you sure to submit')" type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <a href="{{route('user-pending-customer')}}" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</a>
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
    let expire_date = document.getElementById('custom_expire_date').value
    // Current date
    var today = new Date();
    // Target date
    var targetDate = new Date(expire_date);
    // Calculate difference in milliseconds
    var diff = (targetDate - today);
    // Convert to days
    var daysDiff = Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;
    console.log('daysDiff', daysDiff);
    var year = today.getFullYear();
    var month = today.getMonth();
    // Note: Months are zero-based in JavaScript (0 = January, 1 = February, ..., 11 = December)
    var lastDay = new Date(year, month + 1, 0).getDate();

    let user_bill = $("#user_bill").val()
    let user_discount = $("#user_discount").val()
    let per_day_bill = (user_bill / lastDay).toFixed(2);

    let par_day_discount = (user_discount / lastDay).toFixed(2);
    console.log('par_day_discount', par_day_discount);
    console.log('par_day_discount * daysDiff', par_day_discount * daysDiff);

    const cal = (daysDiff > 28) ? (per_day_bill * daysDiff) - (par_day_discount * daysDiff) : (per_day_bill * daysDiff)

    document.getElementById('amount').value = cal.toFixed(2)
    document.getElementById('received_amount').value = cal.toFixed(2)
  }

  // change duration
  function change_duration() {
    let user_bill = $("#user_bill").val()
    let user_discount = $("#user_discount").val()
    let p_d = $("#custom_payment_duration").val()
    const cal = user_bill * p_d - user_discount * p_d
    document.getElementById('amount').value = cal
    document.getElementById('received_amount').value = cal
  }

  // change duration
  function toggleTransactionIdField(e) {
    if (e.target.value == 'Cash') {
      $('#transaction_id_label').text('Note')
    } else {
      $('#transaction_id_label').text('Transaction Id')
    }
  }
</script>