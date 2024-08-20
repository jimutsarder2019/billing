<?php

namespace App\Console\Commands;

use App\Http\Controllers\Cornjob\ScheduleSendSmsController;
use App\Http\Controllers\customer\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SystemSchedul extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:disconnect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run system operation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            echo "starting... \n";
            $customer =  new Customer();
            // 👉 call customer expiration
            $customer->disconnectExpiredCustomer();

            echo "invoice genereating... \n";
            //👉 call customer invoice generator
            $customer->customer_invoice_createable();

            //👉 call send_sms_before_customer_expire
            $customer->send_sms_before_customer_expire();

            //👉 send sms multipel from store database
            $system_send_sms = new ScheduleSendSmsController();
            $system_send_sms->index();
            echo "\n completed .";
        } catch (\Throwable $th) {
            echo $th;
        }
    }
}
