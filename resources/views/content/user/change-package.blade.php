@extends('layouts/layoutMaster')
@section('title') change Package @endsection
@section('content')
<div class="card p-3 p-md-5">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="text-center mb-4">
                    <h3 class="mb-2">Change Package</h3>
                    <h5 class="mb-2">Wallet: {{$user->wallet ?? 0}} TK</h5>
                </div>
                <form action="{{route('update-customer-package', $user->id)}}" class="row g-3" method="POST">
                    @method('put')
                    @csrf
                    <div class="col-12 text-center mt-2">
                        <table class="table border">
                            <tr>
                                <td>Mikrotik</td>
                                <td>:</td>
                                <td>{{$user->mikrotik->identity}} | {{$user->mikrotik->host}}</td>
                            </tr>
                            <tr>
                                <td>Package</td>
                                <td>:</td>
                                <td>{{$user->package->name}}</td>
                            </tr>
                            <tr>
                                <td>Username</td>
                                <td>:</td>
                                <td>{{$user->username}}</td>
                            </tr>
                            <tr>
                                <td>Bill</td>
                                <td>:</td>
                                <td>{{$user->bill}}</td>
                            </tr>
                            <tr>
                                <td>Discount</td>
                                <td>:</td>
                                <td>{{$user->discount??0}} TK</td>
                            </tr>
                            <tr>
                                <td>Customer Expire Date</td>
                                <td>:</td>
                                <td>{{$user->expire_date}}</td>
                            </tr>
                            @if($user->allow_grace)
                            <tr class="text-danger">
                                <td>Grace Day</td>
                                <td>:</td>
                                <td>{{$user->allow_grace}}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <input type="hidden" name="invoice_for" value="change_package"> <!-- dont remove -->
                    <div id="wallet_cal_balance"></div>
                    <div id="summary-cal"></div>
                    <input type="hidden" name="user_current_pkg_id" value="{{$user->package_id}}">
                    <input type="hidden" name="customer_current_bill" value="{{$user->bill}}">
                    <div class="col-12 mt-1">
                        <label class="form-label w-100" for="instant_change">Change Expire date
                            <i data-bs-toggle="tooltip" data-bs-placement="top" title="Schedule for change package after expire || Expire date when regular extendes 1 month || 
                            if Custom expire date choose a new expire date" class="menu-icon bi bi-info-circle"></i></label>
                        @if($errors->has('expire_date')) <br> <span class="text-danger"> {{$errors->first('expire_date')}}</span> @endif
                        <div class="d-flex">
                            @can('Schedule Package Change')
                            <div class="">
                                <input type="radio" class="form-check-input" onchange='changePackage({{$user->id}},"change_type")' name="expire_date" id="schedule" value="schedule">
                            </div>
                            <div class="mx-2">
                                <label class="form-label w-100" for="schedule">Schedule Change</label>
                            </div>
                            @endcan
                            @can('Change Package Regular Extend Method')
                            <div class="">
                                <input type="radio" class="form-check-input" onchange='changePackage({{$user->id}},"change_type")' name="expire_date" id="instant_change" value="instant_change">
                            </div>
                            <div class="mx-2">
                                <label class="form-label w-100" for="instant_change">Regular Extend Method </label>
                            </div>
                            @endcan
                            @can('Change Package Custom Expire Date')
                            <div class="mx-2">
                                <input type="radio" class="form-check-input" onchange='changePackage({{$user->id}},"change_type")' name="expire_date" id="schedule_expire_date" value="custom_expire_date">
                            </div>
                            <div>
                                <label class="form-label w-100" for="schedule_expire_date">Custom Expire Date</label>
                            </div>
                            <div class="ms-3">
                                <input type="datetime-local" class="form-control d-none" onchange='changePackage({{$user->id}},"change_type")' name="custom_expire_date" id="custom_expire_date">
                            </div>
                            @endcan
                        </div>
                        <label class="form-label" for="Packages">Packages</label>
                        @if($errors->has('package')) <br> <span class="text-danger"> {{$errors->first('package')}}</span> @endif
                        <select id="Packages" name="package" class="form-control" onchange='changePackage({{$user->id}},"change_package")'>
                            <option value="">Select Package</option>
                            @if (auth()->user()->type == FRANCHISE_MANAGER | $user->customer_for == FRANCHISE_MANAGER)
                            <?php
                            $assign_package = App\Models\ManagerAssignPackage::with('package')->where(['manager_id' => auth()->user()->id])->get();
                            ?>
                            @foreach($assign_package as $assign_pack)
                            <?php
                            $franchise_pack_price = $assign_pack->manager_custom_price !== null ? $assign_pack->manager_custom_price : $assign_pack->package->franchise_price
                            ?>
                            <!-- if($assign_pack->package->id !==$user->package->id && $franchise_pack_price > $user->bill ) -->
                            <option class="d-none" value="{{$assign_pack->package->id}}|{{$assign_pack->package->name}}|{{($assign_pack->manager_custom_price !== null ? $assign_pack->manager_custom_price : $assign_pack->package->franchise_price)-$user->discount }}">
                                {{$assign_pack->package->name}} {{$assign_pack->package->synonym ? "|".$assign_pack->package->synonym :''}}|{{$assign_pack->manager_custom_price !== null ? $assign_pack->manager_custom_price : $assign_pack->package->franchise_price }} TK
                            </option>
                            <!-- endif -->
                            @endforeach
                            @else
                            @foreach($packages as $pack)
                            @if($pack->id !==$user->package->id)
                            <option class="d-none" value="{{$pack->id}}|{{$pack->name}}|{{$pack->price-$user->discount}}">
                                {{$pack->name}}{{$pack->synonym ? "|".$pack->synonym :''}}|{{$pack->price }} TK
                            </option>
                            @endif
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <!-- <div class="col-12 mt-2">
                        <label class="form-label w-100" for="start_date">Start Date</label>
                        @if($errors->has('start_date')) <br> <span class="text-danger"> {{$errors->first('start_date')}}</span> @endif
                        <input id="start_date" name="start_date" class="form-control" value="{{old('start_date')}}" type="datetime-local" />
                    </div> -->
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="amount">Total Amount</label>
                        @if($errors->has('amount')) <br> <span class="text-danger"> {{$errors->first('amount')}}</span> @endif
                        <input readonly id="amount" name="amount" class="form-control" value="{{old('amount')}}" type="text" />
                    </div>
                    <!-- <div class="col-12 mt-2">
                        <label class="form-label w-100" for="received_amount">Received Amount</label>
                        @if($errors->has('received_amount')) <br> <span class="text-danger"> {{$errors->first('received_amount')}}</span> @endif
                        <input readonly id="received_amount" name="received_amount" value="{{old('received_amount')}}" class="form-control" type="text" />
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="paid_by">Paid By</label>
                        @if($errors->has('paid_by')) <br> <span class="text-danger"> {{$errors->first('paid_by')}}</span> @endif
                        <select id="paid_by" name="paid_by" class="form-control">
                            <option value="Bkash">Bkash</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="transaction_id">Transaction Id</label>
                        <input id="transaction_id" name="transaction_id" class="form-control" type="text" />
                    </div> -->
                    <div class="col-12 text-center mt-4">
                        <button onclick="return confirm('are you sure to change it!')" id="submit_btn" type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href="{{url()->previous()}}" class="btn btn-label-secondary btn-reset">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


@push('pricing-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function changePackage(user_id, type) {
        var expire_date_radio = $('input[name="expire_date"]:checked')[0].value;
        var expire_date = null;
        var package = $('select[name="package"]')[0].value;
        if (type !== 'change_package') this.ctrl_pkg_list()
        if (!package) return
        const change_pack_info = package.split("|");
        document.getElementById('amount').value = change_pack_info[2]
        if (expire_date_radio !== 'schedule') {
            if (expire_date_radio == 'custom_expire_date') {
                $('#custom_expire_date').removeClass('d-none')
                var pik_expire_date = $('input[name="custom_expire_date"]')[0].value;
                console.log('pik_expire_date', pik_expire_date);
                if (!pik_expire_date) {
                    window.alert('Expire date is required');
                    event.preventDefault();
                    return
                } else {
                    expire_date = pik_expire_date;
                }
            } else {
                $('#custom_expire_date').addClass('d-none')
                $('input[name="custom_expire_date"]')[0].value = '';
            }
            let app_url = document.head.querySelector('meta[name="app_url"]').content;
            axios.put(`${app_url}/customer-req-chage-package/${user_id}?expire_date=${expire_date}`, {
                new_pack: package
            }).then((resp) => {
                console.log(resp.data);
                const data = resp.data.data
                if (resp.data.success) {
                    let html = ''
                    let html_warning = ''
                    let wallet_cal_balance = ''
                    if (expire_date_radio == 'custom_expire_date') {
                        html = `<p class="text-capitalize alert alert-success">Remaining Day: ${data.diff_cmr_exp_date_and_today} Days 
                </br>remaining day current package price: ${data.remaining_day_current_pack_price} TK
                </br>Wallet + remaining Balance: <span class="text-primary font-semibold">${data.remaining_balance}</> TK
                </br>Total Bill for new Expire Date: <span class="text-primary font-semibold">${data.today_and_req_day_new_pack_price}</> TK
                </br>Customer Wallet After Change Package: <span class="text-primary font-semibold">${data.wallet_after_change_packgae}</> TK
                </p>`
                    } else {
                        html = `<p class="text-capitalize alert alert-success">Remaining Day: ${data.remaining_day} Days 
                </br>Remaining Balance: ${data.remaining_balance} TK
                </br>Wallet After Return: ${data.wallet_after_return} TK
                </br>Customer Wallet After Change Package: <span class="text-primary font-semibold">${data.wallet_after_change_packgae}</> TK
                </p>`
                    }
                    wallet_cal_balance = `<input type="hidden" name="wallet_cal_balance" value="${data.wallet_after_change_packgae}">`
                    if (data.wallet_after_change_packgae < 0) {
                        const rechargeable_amount = data.wallet_after_change_packgae.toString();
                        const amount = rechargeable_amount.replace(/-/g, '')
                        html_warning = `<p class=" alert alert-warning">Your wallet doesn't have enough balance.
                     after change this package a new invoice will be created and paid automatically amount ${amount} TK 
                    </p>`
                        // $("#submit_btn").attr("disabled", "disabled");
                    } else {
                        // $("#submit_btn").removeAttr("disabled");
                    }
                    document.getElementById('summary-cal').innerHTML = html + html_warning
                    document.getElementById('wallet_cal_balance').innerHTML = wallet_cal_balance
                } else {
                    document.getElementById('summary-cal').innerHTML = `<span class="text-capitalize alert alert-warning">${resp.data.message}</span>`
                    // $("#submit_btn").attr("disabled", "disabled");
                }
            }).catch(e => {
                document.getElementById('summary-cal').innerHTML = `<p class="text-capitalize alert alert-danger">${e.message}</p>`
                $("#submit_btn").removeAttr("disabled");
            })
        } else {
            $('#custom_expire_date').addClass('d-none')
            document.getElementById('summary-cal').innerHTML = ''
        }
    }


    function ctrl_pkg_list() {
        var expire_date_radio = $('input[name="expire_date"]:checked')[0].value;
        var user_current_pkg_id = $('input[name="user_current_pkg_id"]')[0].value;
        var customer_current_bill = $('input[name="customer_current_bill"]')[0].value;
        var selectElement = document.getElementById("Packages");
        var options = selectElement.options;

        var opt = '<option value="">Select Package</option>'
        for (var i = 1; i < options.length; i++) {
            var option = options[i];
            // console.log(option.value);
            const change_pack_info = option.value.split("|");
            if (expire_date_radio == 'instant_change') {
                opt += `<option class='${parseInt(customer_current_bill) > parseInt(change_pack_info[2]) || parseInt(change_pack_info[0]) == parseInt(user_current_pkg_id) ?'d-none':''}' value="${option.value}">${option.value} TK</option>`
            } else {
                opt += `<option class='${change_pack_info[0] == user_current_pkg_id && expire_date_radio !== 'schedule' ?'d-none':''}' value="${option.value}">${option.value} TK</option>`
            }
        }
        selectElement.innerHTML = opt
    }
</script>
@endpush