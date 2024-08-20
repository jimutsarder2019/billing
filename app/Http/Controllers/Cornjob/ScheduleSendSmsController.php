<?php

namespace App\Http\Controllers\Cornjob;

use App\Http\Controllers\Controller;
use App\Mail\SendEmailTest;
use App\Models\SmsApi;
use App\Models\SmsStore;
use App\Services\Message\MessageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ScheduleSendSmsController extends Controller
{
    public function __construct()
    {
        $this->index();
    }

    function index()
    {
        $smsItems = SmsStore::get();
        try {
            foreach ($smsItems as $key => $item) {
                $reqData = json_decode($item->request);
                $data = SmsApi::select([
                    'id',
                    'name',
                    'api_url',
                    'api_key',
                    'sender_id',
                    'client_id',
                    'desc',
                    'status',
                ])->where('id', $reqData->api !== null ? $reqData->api : 2)->first();
                // check_users
                $message = $reqData->message;
                $user_phone_numbers = $reqData->check_users;
                if ($data && $data->name == 'Brillent') {
                    $api_url = 'https://sms.novocom-bd.com/api/v2/SendSMS';
                    foreach ($user_phone_numbers as $key => $number) {
                        $res = Http::post($api_url, [
                            "SenderId"      => "$data->sender_id",
                            "ApiKey"        => "$data->api_key",
                            "ClientId"      => "$data->client_id",
                            "Message"       => $message,
                            "MobileNumbers" => "$number"
                        ]);
                        MessageService::storeMessage(api_id: $data->id, numbers: $number, message: $message, smsres: $res['Data']);
                    }
                    notify()->success("Message Sent Successfully");
                } elseif ($data && $data->name == 'Reve System') {
                    $callerId =  isset($data->client_id) ? $data->client_id : 'SyncIT BD';
                    foreach ($user_phone_numbers as $key => $number) {
                        $number = str_replace('+', '', $number);
                        $res =  Http::get("$data->api_url/sendtext?apikey=$data->api_key&secretkey=$data->sender_id&callerID=$callerId&toUser=$number&messageContent=$message");;
                        MessageService::storeMessage(api_id: $data->id, numbers: $number, message: $message, smsres: $res);
                    }
                };
                $del_sms_data = SmsStore::where('id', $item->id)->first();
                $del_sms_data->delete();
            }
            // 👉 end foreach
            notify()->success('success');
            return back();
        } catch (\Throwable $th) {
            dd($th);
            notify()->error($th->getMessage());
            return back();
        }
    }
}
