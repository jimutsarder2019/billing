<?php

namespace App\Services\Message;

use App\Models\AdminSetting;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MessageFilterService
{
    /* 
    // accept parameter
    msg
    customer_id 
    */

    public static function filter_message(...$args)
    {
        $args = $args[0];
        $userData = Customer::with('package')->where('id', $args['customer_id'])->first();
        if ($userData) {
            $fldata = $userData->username ?  Str::replace('{user_name}', "$userData->username", $args['msg']) : $fldata =  Str::replace('{user_name}', "", $args['msg']);
            $fldata =  $userData->full_name ? Str::replace('{customer_name}', "$userData->full_name", $fldata) : Str::replace('{customer_name}', "", $fldata);
            $fldata =  $userData->password ? Str::replace('{customer_user_password}', "$userData->password", $fldata) : Str::replace('{customer_user_password}', "", $fldata);
            //customer expire_date
            $exp_date =  $userData->expire_date  ? Carbon::parse($userData->expire_date)->format('Y-m-d h:i:s A') : '';
            $fldata =  Str::replace('{expire_date}', "$exp_date", $fldata);
            // bill
            $fldata =  $userData->package ? Str::replace('{customer_package}', $userData->package->name, $fldata) : Str::replace('{customer_package}', "", $fldata);
            $fldata =  $userData->bill ? Str::replace('{monthly_bill}', $userData->bill - $userData->discount, $fldata) : Str::replace('{monthly_bill}', "", $fldata);
        } else {
            $fldata = $args['msg'];
        }
        if (isset($args['invoice']) && $args['invoice'] !== null) {
            $fldata = Str::replace('{invoice_no}', $args['invoice']['invoice_no'], $fldata);    
            $fldata = Str::replace('{received_amount}', $args['invoice']['received_amount'], $fldata);
            $fldata = Str::replace('{due_amount}', $args['invoice']['due_amount'], $fldata);
        }

        $defaultAdata = AdminSetting::select('slug', 'value')->where('slug', 'site_name')->first();
        $fldata =  $defaultAdata ? Str::replace('{company_name}', "$defaultAdata->value", $fldata) : Str::replace('{company_name}', "", $fldata);
        $fldata =  Str::replace('{CURRENT_DATE}', "'" . Carbon::now() . "'", $fldata);

        $numb = '+88001708169671';
        $company_bkash_number = AdminSetting::select('slug', 'value')->where('slug', 'company_bkash_number')->first();
        $fldata =  Str::replace('{company_bkash_number}', $company_bkash_number ? $company_bkash_number->value : $numb, $fldata);
        $company_roket_number = AdminSetting::select('slug', 'value')->where('slug', 'company_roket_number')->first();
        $fldata =  Str::replace('{company_roket_number}', $company_roket_number ? $company_roket_number->value : $numb, $fldata);
        $company_nagad_number = AdminSetting::select('slug', 'value')->where('slug', 'company_nagad_number')->first();
        $fldata =  Str::replace('{company_nagad_number}', $company_nagad_number ? $company_nagad_number->value : $numb, $fldata);
        return $fldata;
    }
}
