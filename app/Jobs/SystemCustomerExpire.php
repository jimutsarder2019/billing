<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\ConnectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SystemCustomerExpire implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 👉 Create a new job instance.
     */
    public $data;
    public $timeout = 0;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 👉 Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            $expired_package = $this->data['expired_package'];
            $expired_customers = $this->data['customers'];
            foreach ($expired_customers as $key => $ec_item) {
                $connection = new ConnectionService($ec_item->mikrotik->host, $ec_item->mikrotik->username, $ec_item->mikrotik->password, $ec_item->mikrotik->port);
                $connection->disconnectUserProfile($ec_item->id, $ec_item->username, $expired_package->name);
                //don't disconnect user when get data is false
                // related to grace user disconnect 
                Customer::where('id', $ec_item->id)->update([
                    'package_id' => $expired_package->id,
                    'purchase_package_id' => $ec_item->package_id,
                    'status' => CUSTOMER_EXPIRE
                ]);
                if ($ec_item->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_ACCOUNT_EXPIRE, 'number' => $ec_item->phone, 'customer_id' => $ec_item->id]);
                echo "$ec_item->username expired Success \n";
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
        }
    }
}
