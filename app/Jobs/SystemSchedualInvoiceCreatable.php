<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SystemSchedualInvoiceCreatable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;
    public $timeout = 0;
    /**
     * 👉 Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 👉 Execute the job.
     */
    public function handle(): void
    {
        $customers = $this->data['customers'];
        try {
            foreach ($customers as $key => $c_item) {
                $bill = $c_item->bill - $c_item->discount;
                if (!Invoice::select('customer_id', 'invoice_for', 'status')
                    ->where(['customer_id' => $c_item->id, 'invoice_for' => INVOICE_CUSTOMER_MONTHLY_BILL, 'status' => STATUS_PENDING])
                    ->latest()->first()) {
                    if ($c_item->wallet == $bill || $c_item->wallet > $bill) {
                        echo "$c_item->username auto renew \n";
                        $invoice =  Invoice::create([
                            'customer_id'   => $c_item->id,
                            'invoice_no'    => "INV-{$c_item->id}-" . date('m-d-Hms-Y'),
                            'invoice_type'  => INVOICE_TYPE_INCOME,
                            'invoice_for'   => INVOICE_CUSTOMER_MONTHLY_BILL,
                            'manager_for'   => $c_item->customer_for,
                            'package_id'    => $c_item->package_id,
                            'amount'        => $bill,
                            'received_amount' => $bill,
                            'status'        => STATUS_PAID,
                            'comment'       => 'account auto renew',
                            'manager_id'    => $this->data['auth_id'],
                        ]);
                        $expire_date = Carbon::parse($c_item->expire_date)->addMonth();
                        Customer::where('id', $c_item->id)->update([
                            'expire_date' => $expire_date,
                            'wallet' => $c_item->wallet - $bill,
                        ]);
                        if ($c_item->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_CUSTOMER_INV_AUTO_RENEWABLE, 'number' => $c_item->phone, 'customer_id' => $c_item->id, 'invoice' => $invoice]);
                    } else {
                        $invoice =  Invoice::create([
                            'customer_id'   => $c_item->id,
                            'invoice_no'    => "INV-{$c_item->id}-" . date('m-d-Hms-Y'),
                            'invoice_type'  => INVOICE_TYPE_INCOME,
                            'invoice_for'   => INVOICE_CUSTOMER_MONTHLY_BILL,
                            'manager_for'   => $c_item->customer_for,
                            'package_id'    => $c_item->package_id,
                            'amount'        => $bill,
                            'received_amount' => 00,
                            'status'        => STATUS_PENDING,
                            'comment'       => 'auto_generated',
                            'manager_id'    => $this->data['auth_id'],
                        ]);
                        if ($c_item->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_INV_CREATE, 'number' => $c_item->phone, 'customer_id' => $c_item->id, 'invoice' => $invoice]);
                        echo "$c_item->username New Invoice Created \n";
                    }
                }
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}