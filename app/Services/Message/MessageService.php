<?php

namespace App\Services\Message;

use App\Mail\SendEmailTest;
use App\Models\Sentmessage;
use App\Models\SmsApi;
use App\Services\Message\MessageService as MessageMessageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class MessageService
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $number
     * @return \Illuminate\Http\Response $request->message
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $number
     * @return \Illuminate\Http\Response $request->message
     */
    public static function SendMessage($reqData)
    {
        try {
            $data = SmsApi::select([
                'id',
                'name',
                'api_url',
                'api_key',
                'sender_id',
                'client_id',
                'desc',
                'status',
            ])->where('id', $reqData['api'] !== null ? $reqData['api'] : 2)->first();
            // check_users
            $message = $reqData['message'];
            $user_phone_numbers = $reqData['check_users'];
            // ================ testing query start  ================
            // $email = new SendEmailTest($message);
            // test on dev email
            // Mail::to('mdmunirujjaman079@gmail.com')->send($email);
            // return   $store_sms =  MessageService::storeMessage(numbers: "number", message: $message);
            // dd('dd');
            // ============= testing query end =======
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
                    MessageMessageService::storeMessage(api_id: $data->id, numbers: $number, message: $message, smsres: $res['Data']);
                }
                notify()->success("Message Sent Successfully");
            } elseif ($data && $data->name == 'Reve System') {
                $callerId =  isset($data->client_id) ? $data->client_id : 'SyncIT BD';
                foreach ($user_phone_numbers as $key => $number) {
                    $number = str_replace('+', '', $number);
                    $res =  Http::get("$data->api_url/sendtext?apikey=$data->api_key&secretkey=$data->sender_id&callerID=$callerId&toUser=$number&messageContent=$message");;
                    MessageMessageService::storeMessage(api_id: $data->id, numbers: $number, message: $message, smsres: $res);
                }
            };
        } catch (\Throwable $th) {
            // dd($th);
            notify()->error($th->getMessage());
            return back();
        }
    }
    // $res =  Http::get("https://smpp.ajuratech.com:7790/getstatus?apikey=$data->api_key&secretkey=$data->sender_id&callerID=$data->client_id&messageid=37640260");
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeMessage(...$args)
    {
        try {
            $smsres =  isset($args['smsres']) ? json_decode($args['smsres']) : [];
            if ($smsres) {
                $data = Sentmessage::create([
                    'number' => $args['numbers'] ?? '',
                    'sms_apis_id' => $args['api_id'] ?? 'null',
                    'message_id' => isset($smsres->Message_ID) ? $smsres->Message_ID : ($args['smsres'][0]['MessageId'] ?  $args['smsres'][0]['MessageId'] : ''),
                    'users_id' => $args['u_id'] ?? null,
                    'message' => $args['message'] ?? '',
                    'status' => isset($smsres->Text) ? $smsres->Text : (isset($args['smsres'][0]['MessageErrorDescription']) ? $args['smsres'][0]['MessageErrorDescription'] : ''),
                    'status_code' => isset($smsres->Status) ? $smsres->Status : ((isset($args['smsres'][0]['MessageErrorCode'])) ? $args['smsres'][0]['MessageErrorCode'] : ''),
                ]);
            } else {
                $data = Sentmessage::create([
                    'number' => $args['numbers'] ?? '',
                    'message' => $args['message'] ?? '',
                ]);
            }
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
        }
    }
}
