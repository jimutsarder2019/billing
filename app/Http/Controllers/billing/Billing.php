<?php

namespace App\Http\Controllers\billing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\CustomerBalanceHistory;
use App\Models\CustomerGraceHistorys;
use App\Models\Invoice;
use App\Models\Manager;
use App\Models\ManagerBalanceHistory;
use App\Models\SmsTemplates;
use App\Services\ConnectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Billing extends Controller
{
    /**
     * 👉 Store Invoice newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeInvoice(Request $request)
    {
        $request->validate([
            'user_id'           => 'required',
            'invoice_for'       => 'required',
            'amount'            => 'required',
            'received_amount'   => 'required',
            'paid_by'           => 'required',
        ]);
        DB::beginTransaction();
        try {
            $auth = auth()->user();
            $customer =  Customer::with('mikrotik')->where('id', $request->user_id)->first();
            if (!$customer) notify()->error('Customer Not Found');
            $status  = $request->amount > $request->received_amount ? STATUS_DUE : ($request->amount < $request->received_amount ? STATUS_OVER_PAID : STATUS_PAID);
            $inv =  Invoice::create([
                'customer_id'       => $customer->id,
                'invoice_no'        => "INV-{$customer->id}-" . date('m-d-Hms-Y'),
                'invoice_for'       => $request->invoice_for,
                'package_id'        => $customer->package_id,
                'amount'            => $request->amount,
                'received_amount'   => $request->received_amount,
                'due_amount'        => $status == STATUS_DUE ? $request->amount - $request->received_amount : 00,
                'advanced_amount'   => $status == STATUS_OVER_PAID ? $request->received_amount - $request->amount : 00,
                'status'            => $status,
                'customer_pkg_id_when_inv_payment' => $customer->package_id,
                'paid_by'           => $request->paid_by,
                'customer_old_expire_date' => $customer->expire_date,
                'customer_status'   => $customer->status,
                'invoice_type'      => INVOICE_TYPE_INCOME,
                'manager_for'       => $auth->type,
                'comment'           => 'manually_created',
                'transaction_id'    => $request->transaction_id,
                'manager_id'        => $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id,
            ]);
            // 👉 customer increament balance if invoice type  == customer_add_balance
            $amount = $status == STATUS_OVER_PAID ? $request->received_amount - $request->amount : $request->amount;
            if ($request->invoice_for == INVOICE_CUSTOMER_ADD_BALANCE) {
                Customer::select('id', 'wallet')->where('id', $customer->id)->increment('wallet', $amount);
                // 👉 customer balance history
                CustomerBalanceHistory::create([
                    'customer_id'    => $customer->id,
                    'manager_id'     => $auth->id,
                    'balance'        => $amount,
                    'update_Reasons' => 'Add Balance'
                ]);
            }
            // 👉 check if manager is franchise
            if ($status !== STATUS_DUE && $customer->customer_for == FRANCHISE_MANAGER && $request->invoice_for == INVOICE_CUSTOMER_ADD_BALANCE) {
                $manager = Manager::select('id', 'panel_balance')->where('id', $customer->manager_id)->first();
                $manager->decrement('panel_balance', $amount);
                if ($manager->panel_balance < $request->received_amount) return error_message('No Enougn Panel Balance');
                // 👉 manager add balance 
                ManagerBalanceHistory::create([
                    'manager_id'    => $auth->id,
                    'balance'       => $amount,
                    'history_for'   =>  $request->invoice_for,
                    'franchise_panel_balance' => $auth->panel_balance,
                    'sign'          => '-',
                    'invoice_id'    => $inv->id,
                    'status'        => STATUS_ACCEPTED,
                ]);
            } else {
                ManagerBalanceHistory::create([
                    'manager_id'    => $auth->id,
                    'balance'       => $request->received_amount,
                    'history_for'   => $request->invoice_for,
                    'franchise_panel_balance' => $auth->wallet +  $request->received_amount,
                    'sign'          => '+',
                    'status'        => STATUS_ACCEPTED,
                    'invoice_id'    => $inv->id
                ]);
            }
            if ($status !== STATUS_DUE && $customer->customer_for == FRANCHISE_MANAGER && $request->invoice_for == INVOICE_CUSTOMER_MONTHLY_BILL) {
                $manager = Manager::select('id', 'panel_balance')->where('id', $customer->manager_id)->first();
                $manager->decrement('panel_balance', $amount);
                if ($manager->panel_balance < $request->received_amount) return error_message('No Enougn Panel Balance');
                ManagerBalanceHistory::create([
                    'manager_id'    => $auth->id,
                    'balance'       => $amount,
                    'history_for'   =>  $request->invoice_for,
                    'franchise_panel_balance' => $auth->panel_balance,
                    'sign'          => '-',
                    'invoice_id'    => $inv->id,
                    'status'        => STATUS_ACCEPTED,
                ]);
            }

            // 👉 auth manager increment wallet
            Manager::where('id', $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id)->increment('wallet', $request->received_amount);
            // 👉 check and assign sms temp type
            $sms_temp = '';
            if ($request->invoice_for == INVOICE_CUSTOMER_MONTHLY_BILL) {
                $sms_temp = TMP_INV_PAYMENT;
            } elseif ($request->invoice_for == INVOICE_CUSTOMER_ADD_BALANCE) {
                $sms_temp = TMP_CUSTOMER_NEW_BALANCE;
            }
            $sms_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', $sms_temp)->first();
            // add extra
            // 👉 when type monthly_bill call mikrotik if customer is expire and update customer table \
            if ($request->invoice_for == INVOICE_CUSTOMER_MONTHLY_BILL) $this->customerBillPayment($request, $status, $customer, $inv);
            if ($request->is_send_sms && $request->is_send_sms == '1') {
                if (!$sms_tamp && $request->invoice_for !== INVOICE_CONNECTION_FEE) return error_message("Sms Template Not Found for " . $sms_temp);
                // 👉 send sms
                if ($request->invoice_for !== INVOICE_CONNECTION_FEE && $customer->customer_for == APP_MANAGER && ($request->invoice_for == INVOICE_CUSTOMER_ADD_BALANCE || $request->invoice_for == INVOICE_CUSTOMER_MONTHLY_BILL)) {
                    // 👉 send Customer sms
                    if ($customer->customer_for == APP_MANAGER) {
                        SendSingleMessage([
                            'template_type' => $sms_temp,
                            'number'        => $customer->phone,
                            'customer_id'   => $customer->id,
                            'invoice'       => $inv
                        ]);
                    }
                }
            }
            //👉 update invoice expire date 
            $customer =  Customer::where('id', $request->user_id)->first();
            $inv->expire_date  = $customer->expire_date;
            $inv->save();
            //👉 update customer expire date and seve data
            notify()->success("Invoice Create Successfully");
            DB::commit();
            return redirect()->route('invoice.index');
        } catch (\Throwable $th) {
            dd($th);
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     * 👉 Show the form for creating a new resource.
     */
    public function customerBillPayment($request, $status, $customer, $inv)
    {
        // 👉 customer monthly bill payment
        DB::beginTransaction();
        try {
            if ($customer->allow_grace !== null) {
                $customer_grace_info =  CustomerGraceHistorys::where('customer_id', $customer->id)->latest()->first();
                $today = Carbon::now();
                $diffFromToday = $today->diffInDays($customer_grace_info->created_at);
                if ($customer_grace_info->created_at->format('d-m-y') == Carbon::now()->format('d-m-y')) {
                    $expire_date = Carbon::now()->addMonth();
                } elseif ($diffFromToday > $customer->allow_grace) {
                    $expire_date = Carbon::now()->addMonth()->subDays($customer->allow_grace);
                } elseif ($diffFromToday < $customer->allow_grace) {
                    $expire_date = Carbon::now()->addMonth()->subDays($customer->allow_grace - $diffFromToday);
                } elseif ($diffFromToday == $customer->allow_grace) {
                    $expire_date = Carbon::now()->addMonth();
                }
            } else {
                $today = Carbon::now();
                if ($today < $customer->expire_date) {
                    $expire_date = Carbon::parse($customer->expire_date)->addMonth();
                } else {
                    $expire_date = Carbon::now()->addMonth();
                }
            }
            // 👉 store data customer current expire date
            $old_expire_date        = $customer->expire_date;
            $customer_old_status    = $customer->status;
            // 👉store data customer current expire date End //
            Invoice::select('id', 'customer_new_expire_date')->find($inv->id)->update(['customer_new_expire_date' => $expire_date]);
            $customer->update([
                'package_id'  => $customer->purchase_package_id,
                'status'      => CUSTOMER_ACTIVE,
                'expire_date' => $expire_date,
                'allow_grace' => null,
                'status'      => CUSTOMER_ACTIVE,
                'is_auto_invoice_create' => STATUS_TRUE,
                'wallet'      => $status == STATUS_OVER_PAID ? $customer->wallet += ($request->received_amount - $request->amount) : $customer->wallet
            ]);
            // 👉check if customer is not active
            $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
            $query_data =  $connection->activeDisconnectedUser($customer, $old_expire_date, $customer_old_status);
            if (gettype($query_data) == 'string') return error_message($query_data);
            // 👉 manager
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
}
