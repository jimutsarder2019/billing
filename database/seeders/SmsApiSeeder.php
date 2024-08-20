<?php

namespace Database\Seeders;

use App\Models\SmsApi;
use Illuminate\Database\Seeder;

class SmsApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrData = array(
            array(
                'id' => '1',
                'name' => 'Brillent',
                'api_url' => '',
                'api_key' => '',
                'sender_id' => '',
                'client_id' => '',
                'status' => 0,
            ),
            array(
                'id' => '2',
                'name' => 'Reve System',
                'api_url' => '',
                'api_key' => '',
                'sender_id' => '',
                'client_id' => 'SyncIT BD',
                'status' => 0,
            ),

        );

        SmsApi::insert($arrData);
    }
}
