<?php

namespace App\Http\Controllers\sms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SmsApi;
use App\Models\Customer;
use App\Models\Manager;
use App\Models\SmsGroup;
use App\Models\SmsGroupUsers;
use App\Models\SmsStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SMS extends Controller
{
    // 👉 view SMS Api
    public function viewSMSApi()
    {
        if (!auth()->user()->can('SMS')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $sms_api = SmsApi::get();
        return view('content.sms.sms-api', compact('sms_api'));
    }
    // 👉 store SMS Api
    public function storeSMSApi(Request $request)
    {
        if (!auth()->user()->can('SMS Api Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name'      => 'required',
            'api'       => 'required',
            'api_key'   => 'required',
            'secret_key' => 'required'
        ]);
        SmsApi::create([
            'name'      => $request->name,
            'api_url'   => $request->api,
            'api_key'   => $request->api_key,
            'sender_id' => $request->secret_key,
            'client_id' => $request->cgeter_id
        ]);
        return back();
    }
    // 👉 create Send Sms
    public function createSendSms()
    {
        $sms_apis = SmsApi::select('id', 'name', 'api_url', 'api_key', 'sender_id', 'client_id', 'desc', 'status')->get();
        $users = [];
        return view('content.sms.send-sms', compact('sms_apis', 'users'));
    }
    // 👉 store Group
    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'user_type' => 'required',
            'users' => 'required'
        ]);
        $group = SmsGroup::create([
            'name' => $request->name,
            'group_type' => $request->user_type,
        ]);
        foreach ($request->users as $value) {
            $typeandid = explode(" ", $value);
            if ($typeandid[0] == 'customer') {
                $customer = Customer::where('id', $typeandid[1])->first();
                SmsGroupUsers::create([
                    'smsgroup_id' => $group->id,
                    'customer_id' => $typeandid[1]
                ]);
            }
            if ($typeandid[0] == 'manager') {
                $manager = Manager::where('id', $typeandid[1])->first();
                SmsGroupUsers::create([
                    'smsgroup_id' => $group->id,
                    'manager_id' => $typeandid[1]
                ]);
            }
        }
        return back();
    }
    // https://smpp.revesms.com/sms/smsConfiguration/smsClientBalance.jsp?client=CLIENT_ID
    // 👉 check_balance
    public function check_balance(Request $request)
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
            ])->where('id', $request->api_id)->first();
            if ($data->name == 'Reve System') {
                $client = $data->client_id ?? 'syncit';
                $res =  Http::get("http://smpp.revesms.com/sms/smsConfiguration/smsClientBalance.jsp?client=syncit");
                return response()->json(['balance' => $res['Balance']]);
            } else {
                $res =  Http::get("$data->api_url/Balance?ApiKey=$data->api_key&ClientId=$data->client_id");
                return response()->json(['balance' => $res['Data'] ? $res['Data'][0]['Credits'] : 0]);
            }
        } catch (\Throwable $th) {
            return error_message($th->getMessage(), $th->getCode());
        }
    }
    // 👉 getGroupUsers
    public function getGroupUsers(Request $request)
    {
        $element = $request->element_id;
        $element = explode('_', $element);
        $sms_users = SmsGroupUsers::where('smsgroup_id', $element[1])->get();
        $users_array = [];
        foreach ($sms_users as $sms_user) {
            array_push($users_array, $sms_user->user);
        }
        return response()->json(['element' => $users_array]);
    }
    // 👉 send msg 
    public function sendSms(Request $request)
    {
        if (is_null($request->user_type))  return error_message('please select one');
        if ($request->confirm) {
            $check_users = $request->check_users;
            if (is_null($check_users)) {
                $user_list = $this->getuserList($request->user_type);
                $data = ['user_type' => $request->input('user_type'), 'message' => $request->input('message'), 'api' => $request->input('api')];
                notify()->warning("Select User");
                return redirect()->back()->with('user_list', $user_list)->withInput($data);
            }
            DB::beginTransaction();
            try {
                SmsStore::create([
                    'request' => json_encode($request->all())
                ]);
                DB::commit();
                notify()->success("SMS Store");
                return back();
            } catch (\Throwable $th) {
                DB::rollBack();
                notify()->warning($th->getMessage());
                return back();
            }
        } else {
            $user_list = $this->getuserList($request->user_type);
            $data = ['user_type' => $request->input('user_type'), 'message' => $request->input('message'), 'api' => $request->input('api')];
            return redirect()->back()->with('user_list', $user_list)->withInput($data);
        }
    }

    // 👉 get user list data
    public function getuserList($arr)
    {
        // 👉 corporate_user dashboard_user active_customer inactive_customer pending_customer
        $list_data = [];
        foreach ($arr as $key => $value) {
            if ($value == 'dashboard_user') {
                $managers = Manager::select('id', 'name', 'phone')->get();
                foreach ($managers as $manager) {
                    $list_data[] = $manager;
                    // if (!$this->phoneExistsInListData($list_data, $manager->phone)) {
                    // }
                }
            }
            if ($value == 'active_customer') {
                $customer = Customer::select('id', 'username', 'phone')->where('status', CUSTOMER_ACTIVE)->get();
                // dd($customer);
                foreach ($customer as $customer) {
                    $list_data[] = $customer;
                    // if (!$this->phoneExistsInListData($list_data, $customer->phone)) {
                    // }
                }
            }
            if ($value == 'inactive_customer') {
                $customer = Customer::select('id', 'username', 'phone')
                    ->where('status', CUSTOMER_EXPIRE)
                    ->orWhere('status', CUSTOMER_DELETE)
                    ->orWhere('status', CUSTOMER_SUSPENDED)
                    ->get();
                foreach ($customer as $customer) {
                    $list_data[] = $customer;
                    // if (!$this->phoneExistsInListData($list_data, $customer->phone)) {
                    // }
                }
            }
            if ($value == 'pending_customer') {
                $customer = Customer::select('id', 'username', 'phone')
                    ->where('status', CUSTOMER_PENDING)
                    ->orWhere('status', CUSTOMER_NEW_REGISTER)
                    ->get();
                foreach ($customer as $customer) {
                    if (!$this->phoneExistsInListData($list_data, $customer->phone)) {
                        $list_data[] = $customer;
                    }
                }
            }
        }
        return $list_data;
    }
    // 👉 list data check and store 
    private function phoneExistsInListData($list_data, $phone)
    {
        foreach ($list_data as $data) {
            if ($data->phone === $phone) {
                return true;
            }
        }
        return false;
    }
}
