<?php

use App\Mail\SendEmailTest;
use Illuminate\Support\Facades\Http;
use App\Models\AdminSetting;
use App\Models\ManagerAssignPackage;
use App\Models\SmsApi;
use App\Models\SmsTemplates;
use App\Services\Message\MessageFilterService;
use App\Services\Message\MessageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

/* 
* 👉 msg, or template_type require
* number, require
* customer_id required
 */

function SendSingleMessage(...$args)
{
    try {
        $args = $args[0];
        $number = $args['number'];
        if (!isset($args['msg']) & !isset($args['template_type'])) {
            notify()->error("msg or template_type is required");
            return back();
        }
        //👉 get sms template 
        $sms_tamplate = isset($args['template_type']) ? SmsTemplates::where('type', $args['template_type'])->first() : null;
        if ($sms_tamplate == null) {
            notify()->error("Template not Found");
            return back();
        }
        // 👉 sms 
        $message = $sms_tamplate ? $sms_tamplate->template : $args['msg'];
        $message = MessageFilterService::filter_message(['msg' => $message, 'customer_id' => $args['customer_id'], 'invoice' => isset($args['invoice']) ? $args['invoice'] : null]);
        $email = new SendEmailTest($message);
        // send test mail in developer gmail 
        Mail::to('mdmunirujjaman079@gmail.com')->send($email);
        $store_sms =  MessageService::storeMessage(numbers: $number, message: $message);
        // dd($strore_sms);
        return true;
        dd('$th');
        //  👉 get sms api data
        $data = SmsApi::select(['id', 'name', 'api_url', 'api_key', 'sender_id', 'client_id', 'desc', 'status',])->where('id', $sms_tamplate ? $sms_tamplate->sms_apis_id : 2)->first();
        //  👉 Check api and send msg  
        if ($data && $data->name == 'Brillent') {
            $api_url = 'https://sms.novocom-bd.com/api/v2/SendSMS';
            //call api 
            $res = Http::post($api_url, ["SenderId" => "$data->sender_id", "ApiKey" => "$data->api_key", "ClientId" => "$data->client_id", "Message" => $message, "MobileNumbers" => "$number"]);
            MessageService::storeMessage(api_id: $data->id, numbers: $number, message: $message, smsres: $res['Data']);
        } elseif ($data && $data->name == 'Reve System') {
            $callerId =  isset($data->client_id) ? $data->client_id : 'SyncIT BD';
            $number = str_replace('+', '', $number);
            // $number = "01836708901";
            //  👉call api 
            // 👉 https://smpp.ajuratech.com:7790
            $res =  Http::get("$data->api_url/sendtext?apikey=$data->api_key&secretkey=$data->sender_id&callerID=$callerId&toUser=$number&messageContent=$message");
            MessageService::storeMessage(api_id: $data->id, numbers: $number, message: $message, smsres: $res);
        };
    } catch (\Throwable $th) {
        return;
        //throw $th;
        notify()->error($th->getMessage());
    }
}

// 👉 throw error msg 
function get_paginate($request, $table)
{
    return   $items = ($request->item && $request->item == 'all') ? DB::table($table)->select('id')->count() : ($request->item && $request->item !== 'all' ? $request->item : 15);
}


// 👉 throw error msg 
function error_message($ms)
{
    notify()->error($ms);
    return back();
}

//👉 global file upload method
function fileUpload($new_file, $path, $old_file_name = NULL)
{
    //  👉 make dir
    if (!file_exists(public_path($path))) mkdir(public_path($path), 0777, TRUE);
    //  👉 remove old file when update 
    if (isset($old_file_name) && $old_file_name != "" && file_exists($old_file_name)) unlink($old_file_name);
    // 👉 file name 
    $file_name = $new_file->getClientOriginalName();
    if (file_exists(public_path($path . $file_name))) {
        if (!File::exists($path . $file_name)) File::makeDirectory($path, 0777, true, true);
        $file_name  = rand(00, 99) . $file_name;
    } else {
        $file_name  = $file_name;
    }
    //  👉move file upload
    $new_file->move($path, $file_name);
    return $path . $file_name;
}

//👉 balance balance
function balance_increment($value, $pdate_for = null,)
{
    $balance = AdminSetting::where('slug', 'balance')->first();
    $balance->value += $value;
    $balance->save();
    return $balance->value;
}

//👉 balance uPdate
function balance_decrement($value)
{
    $balance = AdminSetting::where('slug', 'balance')->first();
    $balance->value -= $value;
    $balance->save();
    return $balance->value;
}

//👉 timeMinToHourMin
function timeMinToHourMin($minutes)
{
    $hours = floor($minutes / 60);
    $min = $minutes - ($hours * 60);

    return ($hours . ":" . $min);
}
/*
*👉 throw franchise  package price
* manager_id requared 
*package_id requared 
*/
function franchise_actual_packge_price($manager_id, $package_id)
{
    $assign_price = ManagerAssignPackage::with('package')->where(['manager_id' => $manager_id, 'package_id' => $package_id])->first();
    return $assign_price->manager_custom_price !== null ? $assign_price->manager_custom_price : $assign_price->package->franchise_price;
}
/*
*👉 format date
* manager_id requared 
*package_id requared 
*/
function format_ex_date($date)
{
    if ($date == null)  return $date;
    $parsedDate = Carbon::parse($date);
    return $parsedDate->format('Y-m-d H:i:s');
}
/*
*👉 format date
* manager_id requared 
*package_id requared 
*/
//filter date range 
function date_range_search($date_range)
{
    $date_range = str_replace(' ', '', explode('to', $date_range));
    $start_date = Carbon::parse($date_range[0])->startOfDay();

    if (count($date_range) > 1) {
        $end_date = Carbon::parse($date_range[1])->endOfDay();
    } else {
        $end_date = Carbon::now()->endOfDay();
    }
    return [$start_date, $end_date];
}

// check_permission
function check_permission($permission)
{
    if (!auth()->user()->can($permission)) {
        notify()->error('You Have No Access Permission');
        return true;
    }
    return false;
}
