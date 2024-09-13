<?php

namespace App\Http\Controllers\customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Zone;
use App\Models\SubZone;
use App\Models\Package;
use App\Models\Mikrotik;
use App\Models\PppUser;
use App\Models\Customer as CustomerModel;
use App\Models\Manager as ManagerModel;
use App\Models\CustomerBalanceHistory;
use App\Models\CustomerEditHistory;
use App\Models\CustomerPackageChangeHistory;
use Illuminate\Support\Facades\DB;
use App\Services\ConnectionService;
use App\Models\Invoice;
use App\Models\Manager;
use App\Models\ManagerAssignPackage;
use App\Models\ManagerAssignZone;
use App\Models\ManagerBalanceHistory;
use App\Models\SmsTemplates;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class Customer extends Controller
{

    public $auth_user;

    public function __construct()
    {
        $this->auth_user = auth()->user();
    }

    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addCustomer()
    {
        if (!auth()->user()->can('Add User')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $zones = Zone::get();
        $subzones = SubZone::get();
        $packages = Package::where('price', '!=', null)->orWhere('price', '>', 0)->get();
        $mikrotiks = Mikrotik::get();
        return view('content.user.add-customer', compact('zones', 'packages', 'mikrotiks', 'subzones'));
    }

    /**
     * 👉 Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCustomer(Request $request)
    {
        if (!auth()->user()->can('Add User')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'phone'     => 'required|min:11|max:11',
            'address'   => 'required',
            'reg_date'  => 'required',
            'package_id'    => 'required|int',
            'bill'          => 'required|int',
            'mikrotik_id'   => 'required|int',
            // 'username'      => 'required|unique,username:mikrotik_id',
            'username' => 'required|unique:customers,username,NULL,id,mikrotik_id,' . $request->mikrotik_id,
            'password'      => 'required'
        ]);
        $data = new  CustomerModel();
        $data->full_name     = $request->name;
        $data->email         = $request->email;
        $data->gender        = $request->gender;
        $data->national_id   = $request->national_id;
        $data->phone         = '88' . $request->phone;
        $data->date_of_birth = $request->dob;
        $data->father_name   = $request->f_name;
        $data->mother_name   = $request->m_name;
        $data->address       = $request->address;
        $data->zone_id       = $request->zone_id;
        $data->sub_zone_id   = $request->sub_zone_id;
        $data->registration_date = format_ex_date($request->reg_date);
        $data->connection_date   = format_ex_date($request->conn_date);
        $data->package_id        = $request->package_id;
        $data->purchase_package_id  = $request->package_id;
        $data->bill          = $request->bill;
        $data->discount      = $request->discount;
        $data->mikrotik_id   = $request->mikrotik_id;
        $data->customer_for  = auth()->user()->type;
        $data->username      = $request->username;
        $data->password      = $request->password;
        $data->manager_id    = auth()->user()->id;
        $data->additional_phone   = json_encode($request->additional_phone);
        $data->save();
		if($data->save()){
			$manager = new ManagerModel();
			$manager->type = 'user';
			$manager->name = $request->name;
			$manager->email = $request->email;
			$manager->password = Hash::make($request->password);
			$manager->phone = $request->phone;
			$manager->address = $request->address;
			
			$manager->save();
		}
		

        if ($request->note) {
            CustomerEditHistory::create([
                'manager_id' => auth()->user()->id,
                'customer_id' => $data->id,
                'subject' => 'created',
                'note' => $request->note,
            ]);
        }

        notify()->success('Successfully!');
        return redirect()->route('user-pending-customer');
    }
    /**
     *👉 Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPackageDetails($id)
    {
        if (auth()->user()->type == FRANCHISE_MANAGER) {
            $bill = franchise_actual_packge_price(auth()->user()->id, $id);
        } else {
            $package = Package::find($id);
            $bill = $package->price;
        }
        return response()->json(['bill' => $bill]);
    }
    /**
     * 👉 Display all customer listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewCustomer(Request $request)
    {
        // dd($request->all());
        // dd(date_range_search($request->date_range));
        if (!auth()->user()->can('User')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $users = CustomerModel::with('mikrotik', 'package')
            ->when($request->search_query, function ($q) use ($request) {
                $searchQuery = '%' . $request->search_query . '%';
                if (auth()->user()->type == FRANCHISE_MANAGER) {
                    return $q->where('manager_id', auth()->user()->id)
                        ->where(function ($query) use ($searchQuery) {
                            $query->where('full_name', 'LIKE', '%' . $searchQuery . '%')
                                ->orWhere('username', 'LIKE', $searchQuery)
                                ->orWhere('phone', 'LIKE', $searchQuery);
                        })
                        ->where('status', '!=', CUSTOMER_DELETE);
                } else {
                    return $q->where('status', '!=', CUSTOMER_DELETE)
                        ->where(function ($query) use ($searchQuery) {
                            $query->where('full_name', 'LIKE', '%' . $searchQuery . '%')
                                ->orWhere('username', 'LIKE', $searchQuery)
                                ->orWhere('phone', 'LIKE', $searchQuery);
                        });
                }
            })
            ->when($request->date_range, function ($q) use ($request) {
                return $q->whereBetween('expire_date', date_range_search($request->date_range));
            })
            ->when($request->select_mikrotik, function ($q) use ($request) {
                if ($request->select_mikrotik !==  'Select Mikrotik') return $q->where('mikrotik_id', $request->select_mikrotik);
            })
            ->when($request->manager, function ($q) use ($request) {
                if ($request->manager !==  'Select Manager') return  $q->where('manager_id', $request->manager);
            })
            ->when($request->orderBy, function ($q) use ($request) {
                $q->orderBy('id', $request->orderBy);
            })
            ->when(auth()->user(), function ($q) {
                if (auth()->user()->type == FRANCHISE_MANAGER) {
                    return $q->where('manager_id', auth()->user()->id);
                }
            })
            ->where('status', '!=', CUSTOMER_DELETE)
            ->latest()->paginate($request->item ?? 10);

        // 👉 active_customer
        $active_customer = CustomerModel::select('status')->when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('manager_id', auth()->user()->id);
            }
        })->where('status', 'active')->count();
        // 👉 pending_customer
        $pending_customer = CustomerModel::select('status')->when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('manager_id', auth()->user()->id);
            }
        })->where('status', 'pending')->count();
        //👉 expire_customer
        $expire_customer = CustomerModel::select('status')->when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('manager_id', auth()->user()->id);
            }
        })->where('status', 'expire')->count();

        $mikrotiks = Mikrotik::when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('id', auth()->user()->mikrotik_id);
            }
        })->get();
        return view('content.user.view-user', compact(
            'users',
            'active_customer',
            'mikrotiks',
            'pending_customer',
            'expire_customer',
        ));
    }
    /**
     * 👉 Display a customer pending listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingCustomer()
    {
        if (!auth()->user()->can('Pending User')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        if (auth()->user()->hasRole(SUPER_ADMIN_ROLE) | auth()->user()->type == 'app_manager') {
            $users = CustomerModel::where('status', 'pending')->latest()->get();
            $mikrotik_users = PppUser::get();
        } else {
            $users = CustomerModel::where(['status' => CUSTOMER_PENDING, 'manager_id' => auth()->user()->id])->latest()->get();
            $mikrotik_users = PppUser::where('manager_id', auth()->user()->id)->get();
        }
        return view('content.user.pending-user', compact('users', 'mikrotik_users'));
    }
    //👉 get all delete_customers
    public function get_all_delete_customers(Request $request)
    {
        if (!auth()->user()->can('Delete Users')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $users = CustomerModel::with('mikrotik')->when($request->search_query, function ($q) use ($request) {
            $searchQuery = '%' . $request->search_query . '%';
            return $q->where('full_name', 'LIKE', '%' . $searchQuery . '%')
                ->orWhere('phone', 'LIKE', $searchQuery);
        })->when(auth()->user(), function ($q) {
            if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                return $q->where('manager_id', auth()->user()->id);
            }
        })
            ->where('status', CUSTOMER_DELETE)
            ->latest()->paginate($request->item ?? 10);
        return view('content.user.delete-users', compact('users'));
    }

    /**
     * 👉 Update for pending approveCustomer the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approveCustomer(Request $request, $id)
    {
        if (!auth()->user()->can('Confirm Payment')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        //  👉 check account expire & invoice createable sms tamplate
        $sms_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', TMP_WELCOME_SMS)->first();
        if ($request->is_send_welcome_sms && !$sms_tamp) return error_message("Sms Template Not Found for " . TMP_WELCOME_SMS);
        $sms_account_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', TMP_CUSTOMER_ACCOUNT_CREATE)->first();
        if ($request->is_send_new_account_sms && !$sms_account_tamp) return error_message("Sms Template Not Found for " . TMP_CUSTOMER_ACCOUNT_CREATE);
        $auth = auth()->user();
        $request->validate([
            'received_amount'   => 'required',
            'paid_by'           => 'required',
            'transaction_id'    => 'required_if:paid_by,Bkash',
        ]);
        $query_data = '';
        DB::beginTransaction();
        try {
            // 👉 find customer user 
            $user = CustomerModel::where(['id' => $id, 'status' => CUSTOMER_PENDING])->first();
            if (!$user) {
                notify()->warning('User Not Found');
                return redirect()->route('user-view-user');
            }
            if ($user->connection_date == null) {
                $user->connection_date = Carbon::now();
                $user->save();
            }
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                if (auth()->user()->panel_balance < $request->received_amount) {
                    $validator = Validator::make($request->all(), [
                        'received_amount' => [function ($attribute, $value, $fail) {
                            $fail('You Have no enough Balance To add customer');
                        }],
                    ]);
                    if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
                }
            }
            // check mikrotik data if exit
            $mikrotik = Mikrotik::where('id', $user->mikrotik_id)->first();
            if (empty($mikrotik)) return error_message('Mikrotik Not found');
            // 👉 create client
            $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
            $query_data = $connection->addUserToMikrotik($user, $request);
            if (gettype($query_data) == 'string') return error_message($query_data);
            $user->expire_date = $query_data;
            $user->status = CUSTOMER_ACTIVE;
            $user->purchase_package_id = $user->package_id;
            $user->save();
            // 👉create and paid invoice    
            $invoice = Invoice::create([
                'customer_id'       => $user->id,
                'invoice_no'        => "INV-{$user->id}-" . date('m-d-Hms-Y'),
                'invoice_for'       => INVOICE_NEW_USER,
                'package_id'        => $user->package_id,
                'zone_id'           => $user->zone_id,
                'sub_zone_id'       => $user->sub_zone_id ?? null,
                'amount'            => $request->received_amount,
                'customer_status'   => $user->status,
                'received_amount'   => $request->received_amount,
                'paid_by'           => $request->paid_by,
                'transaction_id'    => $request->transaction_id,
                'customer_new_expire_date'  => $user->customer_new_expire_date,
                'customer_old_expire_date'  => $user->expire_date,
                'status'        => STATUS_PAID,
                'invoice_type'  => INVOICE_TYPE_INCOME,
                'manager_for'   => $auth->type,
                'comment'       => 'New User' . ($request->custom_payment_duration ? ' Payment Bill For ' . $request->custom_payment_duration . 'Months' : '') . ($request->custom_expire_date ? 'Custom Expire Date ' . $request->custom_expire_date : ''),
                'manager_id'    => Auth::user()->id,
            ]);
            // 👉 instart customer package history
            CustomerPackageChangeHistory::create(['customer_id' => $user->id, 'package_id' => $user->package_id, 'manager_id' => $auth->id, 'expire_date' => $user->expire_date]);

            // caclculate different day from expire date bill and today for dicscount
            // $expireDate = Carbon::parse($query_data);
            // $currentDate = Carbon::now();
            // $diffInDays = $currentDate->diffInDays($expireDate);
            // 👉 update manager balance 
            // $manager_balance = $auth->type == FRANCHISE_MANAGER ? $request->received_amount - $user->discount : $request->received_amount;

            $manager_balance = $request->received_amount;
            Manager::where('id', $auth->id)
                ->when(
                    $auth->type == FRANCHISE_MANAGER,
                    function ($query) use ($manager_balance) {
                        $query->decrement('panel_balance', $manager_balance);
                    }
                )
                ->when(
                    function ($query) use ($manager_balance) {
                        $query->increment('wallet', $manager_balance);
                    }
                );
            ManagerBalanceHistory::create([
                'manager_id'    => $auth->id,
                'balance'       => $manager_balance,
                'history_for'   => INVOICE_MANAGER_RECEIVED,
                'sign'          => '+',
                'franchise_panel_balance' => $auth->type == APP_MANAGER ? $auth->wallet : $auth->panel_balance,
                'status'        => STATUS_ACCEPTED
            ]);
            // 👉 send customer 
            if ($user->customer_for == APP_MANAGER) {
                if ($request->is_send_welcome_sms) {
                    // 👉 send welcome Msg when custoner active account
                    SendSingleMessage([
                        'template_type' => TMP_WELCOME_SMS,
                        'number'        => $user->phone,
                        'customer_id'   => $user->id,
                        'invoice'       => $invoice
                    ]);
                }
                // 👉 send Account create sms
                if ($request->is_send_new_account_sms) {
                    SendSingleMessage([
                        'template_type' => TMP_CUSTOMER_ACCOUNT_CREATE,
                        'number'        => $user->phone,
                        'customer_id'   => $user->id,
                        'invoice'       => $invoice
                    ]);
                };
            }
            DB::commit();
            notify()->success('Payment Successfully');
            return redirect()->route('user-view-user');
        } catch (Exception $exception) {
            DB::rollBack();
            notify()->error($exception->getMessage());
            return back();
        }
    }
    /**
     * 👉 Show the form for customer editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editCustomer($id)
    {
        if (!auth()->user()->can('User Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $user = CustomerModel::where('id', $id)->first();
        $zones = Zone::get();
        $subzones = null;
        if ($user->zone_id) $subzones = SubZone::where('zone_id', $user->zone_id)->get();
        $packages = Package::where('price', '!=', null)->orWhere('price', '>', 0)->get();
        $mikrotiks = Mikrotik::get();
        return view('content.user.edit-user', compact('user', 'zones', 'packages', 'mikrotiks', 'subzones'));
    }
    /**
     * 👉 Update the customer specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCustomer(Request $request, $id)
    {
        if (!auth()->user()->can('User Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $request->validate([
                'name'  => 'required',
                'phone' => "required",
            ]);
            $user = CustomerModel::find($id);
            $user->full_name            = $request->name ?? $user->full_name;
            $user->email                = $request->email ?? $user->email;
            $user->gender               = $request->gender ?? $user->gender;
            $user->national_id          = $request->national_id ?? $user->national_id;
            $user->phone                = $request->phone ?? $user->phone;
            $user->additional_phone     = $request->additional_phone ? json_encode($request->additional_phone) : $user->additional_phone;
            $user->date_of_birth        = $request->dob ?? $user->date_of_birth;
            $user->father_name          = $request->f_name ?? $user->father_name;
            $user->mother_name          = $request->m_name ?? $user->mother_name;
            $user->address              = $request->address ?? $user->address;
            $user->zone_id              = $request->zone_id ?? $user->zone_id;
            $user->sub_zone_id          = $request->sub_zone_id ?? $user->sub_zone_id;
            $user->registration_date    = $request->reg_date ?? $user->registration_date;
            $user->connection_date      = $request->conn_date ?? $user->connection_date;
            $user->package_id           = $request->package_id ?? $user->package_id;
            $user->bill                 = $request->bill ?? $user->bill;
            $user->discount             = $request->discount;
            $user->customer_for         = auth()->user()->type ?? $user->customer_for;
            $user->manager_id           = auth()->user()->id ?? $user->manager_id;
            $user->save();
            if ($request->note) {
                CustomerEditHistory::create([
                    'customer_id' => $user->id,
                    'manager_id' => auth()->user()->id,
                    'subject' => 'Updated',
                    'note' => $request->note,
                ]);
            }
            notify()->success('Update Successfully');
            return redirect()->route('user-view-user');
        } catch (\Throwable $th) {
            return error_message($th);
        }
    }
    /**
     * Show the form for editMikrotikCustomer editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editMikrotikCustomer($id)
    {
        try {
            $user = PppUser::where('id', $id)->first();

            $zones = Zone::when(auth()->user(), function ($q) {
                if (auth()->user()->type == FRANCHISE_MANAGER) {
                    $assigned_zone = ManagerAssignZone::where('manager_id', auth()->user()->id)->pluck('zone_id');
                    return $q->whereIn('id', $assigned_zone);
                }
            })->get();
            $packages = Package::where('price', '!=', null)->orWhere('price', '>', 0)->get();
            $mikrotiks = Mikrotik::get();
            return view('content.user.edit-mikrotik-user', compact('user', 'zones', 'packages', 'mikrotiks'));
        } catch (\Throwable $th) {
            return error_message($th);
        }
    }

    /**
     * 👉 Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeMikrotikCustomer(Request $request)
    {
        $auth_user = auth()->user();
        $request->validate([
            'id_in_mkt' => 'required',
            'name'      => 'required',
            'phone'     => 'required',
            'package_id' => 'required',
            'zone_id'    => 'required',
            'expire_date' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $expire_package = AdminSetting::where('slug', 'disconnect_package')->first();
            if (!$expire_package) return error_message('disconnect Package Not Found');
            $package = Package::select('id', 'name')->where('name', $expire_package->value)->first();
            if (!$package) return error_message('Package not found');
            $ppp_user = PppUser::where('name',  $request->username)->first();
            if (!$ppp_user) return error_message('PPP User not found');
            $check_customer = CustomerModel::where('username', $request->username)->first();
            if (!$check_customer) {
                $customer = new CustomerModel();
                $customer->id_in_mkt        = $request->id_in_mkt;
                $customer->full_name        = $request->name;
                $customer->email            = $request->email;
                $customer->gender           = $request->gender;
                $customer->national_id      = $request->national_id;
                $customer->phone            = $request->phone;
                $customer->date_of_birth    = $request->dob;
                $customer->father_name      = $request->f_name;
                $customer->mother_name      = $request->m_name;
                $customer->address          = $request->address;
                $customer->zone_id          = $request->zone_id;
                $customer->sub_zone_id      = $request->sub_zone_id;
                $customer->registration_date = format_ex_date($request->reg_date);
                $customer->connection_date   = format_ex_date($request->conn_date);
                $customer->expire_date       = format_ex_date($request->expire_date);
                $customer->package_id        = $request->package_id;
                $customer->purchase_package_id = $request->package_id;
                $customer->bill               = $request->bill;
                $customer->discount           = $request->discount;
                $customer->status             = $package->id == $request->package_id ? CUSTOMER_EXPIRE : CUSTOMER_ACTIVE;
                $customer->customer_for  = auth()->user()->type;
                $customer->mikrotik_id   = $ppp_user->mikrotik_id;
                $customer->username      = $ppp_user->name;
                $customer->password      = $ppp_user->password;
                $customer->manager_id    = auth()->user()->id;
                $customer->save();
                if ($auth_user->type == FRANCHISE_MANAGER) {
                    // caclculate for expire date bill
                    $expireDate = Carbon::parse($customer->expire_date);
                    $currentDate = Carbon::now();
                    $totalDaysInMonth = $expireDate->daysInMonth;
                    /* 
                    * comment 
                    * $today_day = $currentDate->day;
                    * $this_month_remaining_day = $totalDaysInMonth - $today_day;
                    * comment  
                    */
                    $per_day_bill = $customer->bill / $totalDaysInMonth;
                    $diffInDays = $currentDate->diffInDays($expireDate);
                    $formattedNumber = round($per_day_bill, 2);
                    // check discount
                    $discount =  ($diffInDays >= 30) ? $customer->discount : 0;
                    $bill_for_next_exp_date = sprintf(round($formattedNumber * $diffInDays)) - $discount;
                    // 👉create and paid invoice
                    $inv = Invoice::create([
                        'customer_id'       => $customer->id,
                        'invoice_no'        => "INV-{$customer->id}-" . date('m-d-Hms-Y'),
                        'invoice_for'       => INVOICE_NEW_USER,
                        'package_id'        => $customer->package_id,
                        'zone_id'           => $customer->zone_id,
                        'amount'            => $bill_for_next_exp_date,
                        'received_amount'   => $bill_for_next_exp_date,
                        'paid_by'           => 'Cash',
                        'customer_old_expire_date'  => $customer->expire_date,
                        'customer_status'   => $customer->status,
                        'transaction_id'    => $request->transaction_id,
                        'status'        => STATUS_PAID,
                        'invoice_type'  => INVOICE_TYPE_INCOME,
                        'manager_for'   => $auth_user->type,
                        'comment'       => 'New User',
                        'manager_id'    => $auth_user->id,
                    ]);
                    Manager::where('id', $auth_user->id)->decrement('panel_balance', $bill_for_next_exp_date);
                    ManagerBalanceHistory::create([
                        'manager_id'    => $auth_user->id,
                        'balance'       => $bill_for_next_exp_date,
                        'history_for'   =>  $inv->invoice_for,
                        'franchise_panel_balance' => $auth_user->panel_balance,
                        'sign'          => '-',
                        'invoice_id'    => $inv->id,
                        'status'        => STATUS_ACCEPTED,
                    ]);
                }
            }
            // update ppp user table 
            $ppp_user->update(['added_in_customers_table' => true]);
            DB::commit();
            notify()->success('Update Succssfully');
            return redirect()->route('user-view-user');
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            DB::rollBack();
            return back();
        }
    }
    /**
     * 👉 Disconnect customer or create invoce
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function try_to_test_code(Request $request)
    {
        try {
            $ec_item = CustomerModel::select(
                'id',
                'status',
                'phone',
                'customer_for',
                'expire_date',
                'bill',
                'username',
                'mikrotik_id',
                'phone',
                'purchase_package_id',
                'package_id',
                'is_schedule_package_change',
                'schedule_package_id',
                'schedule_package_bill',
                'is_sms_sent_before_expire',
            )->with('mikrotik')->find(82);
            if ($ec_item->is_schedule_package_change) {
                // change package with auto schedule
                $n_pack = Package::where('id', $ec_item->schedule_package_id)->first();
                // add customer new expire date
                $cmr_new_ex_date = Carbon::parse($ec_item->expire_date)->addMonth();
                $request['package'] = "$n_pack->id|$n_pack->name";
                $request['custom_expire_date'] = $cmr_new_ex_date;
                // call mikrotik api
                $connection = new ConnectionService($ec_item->mikrotik->host, $ec_item->mikrotik->username, $ec_item->mikrotik->password, $ec_item->mikrotik->port);
                $res_query = $connection->changeCustomerPackage($ec_item, $request);
                if (gettype($res_query) == 'string') return error_message($res_query);

                /* 👉 don't disconnect user when get data is false */
                CustomerModel::where('id', $ec_item->id)->update([
                    'purchase_package_id' => $n_pack->id,
                    'package_id' => $n_pack->id,
                    'expire_date' => $cmr_new_ex_date,
                    'bill' => $ec_item->schedule_package_bill,
                    'status' => CUSTOMER_ACTIVE,
                    'is_sms_sent_before_expire' => STATUS_FALSE,
                    'is_schedule_package_change' => STATUS_FALSE,
                    'schedule_package_id' => null,
                    'schedule_package_bill' => null,
                ]);
                echo "$ec_item->username Schedule Package update \n";
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }
    /**
     * 👉 Disconnect customer or create invoce
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function disconnectExpiredCustomer()
    {
        try {
            CustomerModel::select(
                'id',
                'status',
                'phone',
                'customer_for',
                'expire_date',
                'bill',
                'username',
                'mikrotik_id',
                'phone',
                'purchase_package_id',
                'package_id',
                'is_schedule_package_change',
                'schedule_package_id',
                'schedule_package_bill',
                'is_sms_sent_before_expire',
            )
                ->where('status', CUSTOMER_ACTIVE)
                ->where('expire_date', '<=', Carbon::now())
                ->with('mikrotik')
                ->chunk(100, function ($expired_customers) {
                    $system_disconnect_package = AdminSetting::select('id', 'slug', 'value')->where('slug', 'disconnect_package')->first();
                    if (!$system_disconnect_package) return error_message('disconnect package not Set in System Settings');
                    $expired_package = Package::select('id', 'name', 'mikrotik_id')->where('name', $system_disconnect_package->value)->first();
                    if (!$expired_package) return error_message('disconnect package not Update in package list. Please Check your expire package');
                    foreach ($expired_customers as $ec_item) {
                        if ($ec_item->is_schedule_package_change) {
                            // change package with auto schedule
                            $n_pack = Package::where('id', $ec_item->schedule_package_id)->first();
                            // add customer new expire date
                            $cmr_new_ex_date = Carbon::parse($ec_item->expire_date)->addMonth();
                            $request['package'] = "$n_pack->id|$n_pack->name";
                            $request['custom_expire_date'] = $cmr_new_ex_date;
                            // call mikrotik api
                            $connection = new ConnectionService($ec_item->mikrotik->host, $ec_item->mikrotik->username, $ec_item->mikrotik->password, $ec_item->mikrotik->port);
                            $res_query = $connection->changeCustomerPackage($ec_item, $request);
                            if (gettype($res_query) == 'string') return error_message($res_query);
                            /* 👉 don't disconnect user when get data is false */
                            CustomerModel::where('id', $ec_item->id)->update([
                                'purchase_package_id' => $n_pack->id,
                                'package_id' => $n_pack->id,
                                'expire_date' => $cmr_new_ex_date,
                                'bill' => $ec_item->schedule_package_bill,
                                'status' => CUSTOMER_ACTIVE,
                                'is_sms_sent_before_expire' => STATUS_FALSE,
                                'is_schedule_package_change' => STATUS_FALSE,
                                'schedule_package_id' => null,
                                'schedule_package_bill' => null,
                            ]);
                            echo "$ec_item->username Schedule Package update \n";
                        } else {
                            //👉 Conncet with mikrotik
                            try {
                                $connection = new ConnectionService($ec_item->mikrotik->host, $ec_item->mikrotik->username, $ec_item->mikrotik->password, $ec_item->mikrotik->port);
                                $res =  $connection->disconnectUserProfile($ec_item->id, $ec_item->username, $expired_package->name);
                            } catch (\Throwable $th) {
                            }
                            /* 👉 don't disconnect user when get data is false */
                            CustomerModel::where('id', $ec_item->id)->update([
                                'purchase_package_id' => $ec_item->package_id,
                                'package_id' => $expired_package->id,
                                'is_sms_sent_before_expire' => 0,
                                'status' => CUSTOMER_EXPIRE
                            ]);
                            $sms_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', TMP_ACCOUNT_EXPIRE)->first();
                            if ($sms_tamp) {
                                if ($ec_item->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_ACCOUNT_EXPIRE, 'number' => $ec_item->phone, 'customer_id' => $ec_item->id]);
                            }
                            echo "$ec_item->username Expired \n";
                        }
                    };
                });
            notify()->success('System Run successfully');
            return back();
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
        }
    }
    /**
     * 👉 create invoice and send sms a new resource.
     */
    public function send_sms_before_customer_expire()
    {
        try {
            $before_expire_customer = AdminSetting::select('slug', 'value')->where('slug', 'expire_before_msg')->first();
            if (!$before_expire_customer) return;
            //  👉 check account expire & invoice createable sms tamplate
            $sms_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', SEND_SMS_BEFORE_CUSTOMER_EXPIRE)->first();
            if (!$sms_tamp) return;
            //👉 create and send sms when create invoice after  inv_g_day
            $hours = Carbon::now()->addHour($before_expire_customer ? $before_expire_customer->value : 3);
            CustomerModel::select(
                'id',
                'customer_for',
                'package_id',
                'phone',
                'bill',
                'username',
                'discount',
                'wallet',
                'allow_grace',
                'is_sms_sent_before_expire',
                'expire_date'
            )
                ->where(['status' => CUSTOMER_ACTIVE])
                ->whereBetween('expire_date', [Carbon::now(), $hours])
                ->chunk(100, function ($customer) {
                    foreach ($customer as $key => $c_item) {
                        if ($c_item->is_sms_sent_before_expire == 0) {
                            CustomerModel::where('id', $c_item->id)->update(['is_sms_sent_before_expire' => 1]);
                            if ($c_item->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => SEND_SMS_BEFORE_CUSTOMER_EXPIRE, 'number' => $c_item->phone, 'customer_id' => $c_item->id]);
                            echo "$c_item->username Send SMS Expire After \n";
                        }
                    }
                });
            // DB::commit();
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }
    /**
     *👉 create invoice and send sms a new resource.
     */
    public function customer_invoice_createable()
    {
        try {
            $create_invoice_days = AdminSetting::select('slug', 'value')->where('slug', 'create_invoice_days')->first();
            //👉 create and send sms when create invoice after  inv_g_day
            $inv_g_day = Carbon::now()->addDay($create_invoice_days ? $create_invoice_days->value : 3);
            $da = CustomerModel::select(
                'id',
                'customer_for',
                'package_id',
                'phone',
                'bill',
                'username',
                'discount',
                'wallet',
                'allow_grace',
                'expire_date',
                'manager_id',
            )
                ->where(['status' => CUSTOMER_ACTIVE, 'allow_grace' => null, 'is_auto_invoice_create' => STATUS_TRUE])
                ->whereBetween('expire_date', [Carbon::now()->endOfDay(), $inv_g_day])
                ->chunk(100, function ($invoice_createable_customers) {
                    foreach ($invoice_createable_customers as $key => $c_item) {
                        $bill = $c_item->bill - $c_item->discount;
                        $exists_inv = Invoice::select('customer_id', 'invoice_for', 'status', 'created_at')
                            ->where(['customer_id' => $c_item->id, 'invoice_for' => INVOICE_CUSTOMER_MONTHLY_BILL, 'status' => STATUS_PENDING])
                            ->whereMonth('created_at', '=', Carbon::now()->month)
                            ->latest()->first();
                        if (!$exists_inv) {
                            //expire_date
                            $expire_date = Carbon::parse($c_item->expire_date)->addMonth();
                            if ($c_item->wallet == $bill || $c_item->wallet > $bill) {
                                echo "$c_item->username auto renew \n";
                                //create new invoice
                                $invoice =  Invoice::create([
                                    'customer_id'   => $c_item->id,
                                    'invoice_no'    => "INV-{$c_item->id}-" . date('m-d-Hms-Y'),
                                    'invoice_type'  => INVOICE_TYPE_INCOME,
                                    'invoice_for'   => INVOICE_CUSTOMER_MONTHLY_BILL,
                                    'manager_for'   => $c_item->customer_for,
                                    'package_id'    => $c_item->package_id,
                                    'amount'        => $bill,
                                    'expire_date'   => $expire_date,
                                    'customer_status'   => $c_item->status,
                                    'customer_old_expire_date'  => $c_item->expire_date,
                                    'received_amount'           => $bill,
                                    'status'        => STATUS_PAID,
                                    'comment'       => 'account auto renew',
                                    'manager_id'    => $c_item->manager_id,
                                ]);
                                CustomerModel::where('id', $c_item->id)->update([
                                    'expire_date' => $expire_date,
                                    'wallet' => $c_item->wallet - $bill,
                                    'is_sms_sent_before_expire' => 0,
                                ]);
                                //  👉 check account expire & invoice createable sms tamplate
                                $sms_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', TMP_CUSTOMER_INV_AUTO_RENEWABLE)->first();
                                if ($sms_tamp) {
                                    if ($c_item->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_CUSTOMER_INV_AUTO_RENEWABLE, 'number' => $c_item->phone, 'customer_id' => $c_item->id, 'invoice' => $invoice]);
                                }
                            } else {
                                $invoice =  Invoice::create([
                                    'customer_id'   => $c_item->id,
                                    'invoice_no'    => "INV-{$c_item->id}-" . date('m-d-Hms-Y'),
                                    'invoice_type'  => INVOICE_TYPE_INCOME,
                                    'invoice_for'   => INVOICE_CUSTOMER_MONTHLY_BILL,
                                    'manager_for'   => $c_item->customer_for,
                                    'package_id'    => $c_item->package_id,
                                    'amount'        => $bill,
                                    'expire_date'   => $expire_date,
                                    'customer_status'   => $c_item->status,
                                    'customer_old_expire_date'  => $c_item->expire_date,
                                    'received_amount' => 00,
                                    'status'        => STATUS_PENDING,
                                    'comment'       => 'auto_generated',
                                    'manager_id'    => $c_item->manager_id,
                                ]);
                                CustomerModel::select('id', 'is_auto_invoice_create')->where('id', $c_item->id)->update(['is_auto_invoice_create' => STATUS_FALSE]);
                                $sms_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', TMP_INV_CREATE)->first();
                                if ($invoice && $sms_tamp) {
                                    if ($c_item->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_INV_CREATE, 'number' => $c_item->phone, 'customer_id' => $c_item->id, 'invoice' => $invoice]);
                                }
                                echo "$c_item->username New Invoice Created \n";
                            }
                        }
                    }
                });
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }
    //👉 Cust change Password
    public function customerChangePassword(Request $request, $id)
    {
        if (!auth()->user()->can('User Change Password')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'password' => 'required',
            'password_confirmation' => 'required_with:password|same:password',
        ]);
        DB::beginTransaction();
        try {
            $customer = CustomerModel::with('mikrotik')->find($id);
            $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
            $res_query = $connection->changePassword($customer, $request);
            if (gettype($res_query) == 'string') return error_message($res_query);
            $customer->update(['password' => $res_query[0]['password']]);
            notify()->success('Password Change Successfully');
            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     * 👉 Update the specified change_package.
     * @param  int  $id
     */
    public function change_package($id)
    {
        if (!auth()->user()->can('User Change Package')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $user = CustomerModel::with('package', 'mikrotik')->find($id);
        $packages = Package::where('mikrotik_id', $user->mikrotik_id)->get();
        return view('content.user.change-package', compact('user', 'packages'));
    }

    /**
     *👉 Update the specified Customer Package resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update_customer_package(Request $request, $id)
    {
        $auth = auth()->user();
        if (!auth()->user()->can('User Change Package')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'package'   => 'required',
            'amount'    => 'required',
            'expire_date' => 'required',
        ], [
            'expire_date.required' => 'Change Type is required'
        ]);
        DB::beginTransaction();
        try {
            $req_package = explode('|', $request->package);
            $customer = CustomerModel::with('mikrotik', 'manager')->find($id);
            // if request->expire_date = schedule 
            if ($request->expire_date == 'schedule') {
                if ($req_package[2] > $customer->manager->panel_balance) {
                    $validator = Validator::make($request->all(), [
                        'amount' => [function ($attribute, $value, $fail) {
                            $fail('manager has No enough panel balance');
                        }],
                    ]);
                    if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
                }
                $customer->update([
                    'is_schedule_package_change' => true,
                    'schedule_package_id' => intval($req_package[0]),
                    'schedule_package_bill' => intval($req_package[2]),
                ]);
                $inv =  Invoice::create([
                    'customer_id'       => $customer->id,
                    'invoice_no'        => "INV-{$customer->id}-" . date('m-d-Hms-Y'),
                    'invoice_for'       => INVOICE_CUSTOMER_ADD_BALANCE,
                    'package_id'        => $req_package[0],
                    'customer_pkg_id_when_inv_payment' => $customer->package_id, // customer current package id
                    'amount'            => $req_package[2],
                    'received_amount'   => $req_package[2],
                    'status'            => STATUS_PAID,
                    'paid_by'           => $request->paid_by,
                    'customer_old_expire_date' => $customer->expire_date,
                    'customer_status'   => $customer->status,
                    'invoice_type'      => INVOICE_TYPE_INCOME,
                    'manager_for'       => $customer->manager->type,
                    'comment'           => 'manually_created',
                    'transaction_id'    => $request->transaction_id,
                    'manager_id'        => $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id,
                ]);
                $manager = Manager::select('id', 'wallet', 'panel_balance', 'type')->where('id', $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id)->first();
                $manager->wallet = $manager->wallet + $inv->received_amount;
                if ($manager->type == FRANCHISE_MANAGER) $manager->panel_balance = $manager->panel_balance - $inv->received_amount;
                $manager->save();
            } else {
                $inv =  Invoice::create([
                    'customer_id'       => $customer->id,
                    'invoice_no'        => "INV-{$customer->id}-" . date('m-d-Hms-Y'),
                    'invoice_for'       => INVOICE_CUSTOMER_ADD_BALANCE,
                    'package_id'        => $req_package[0],
                    'customer_pkg_id_when_inv_payment' => $customer->package_id,
                    'amount'            => abs($request->wallet_cal_balance),
                    'received_amount'   => abs($request->wallet_cal_balance),
                    'status'            => STATUS_PAID,
                    'customer_pkg_id_when_inv_payment' => $customer->package_id,
                    'paid_by'           => $request->paid_by,
                    'customer_old_expire_date' => $customer->expire_date,
                    'customer_status'   => $customer->status,
                    'invoice_type'      => INVOICE_TYPE_INCOME,
                    'manager_for'       => $customer->manager->type,
                    'comment'           => 'manually_created',
                    'transaction_id'    => $request->transaction_id,
                    'manager_id'        => $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id,
                ]);

                if ($req_package[2] > $request->amount) {
                    $validator = Validator::make($request->all(), [
                        'amount' => [function ($attribute, $value, $fail) {
                            $fail('amount must be gretter then or same Package price ');
                        }],
                    ]);
                    if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
                }
                // 👉 connect with mikrotik
                $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
                $res_query = $connection->changeCustomerPackage($customer, $request);
                if (gettype($res_query) == 'string') return error_message($res_query);
                if ($request->expire_date == 'custom_expire_date') {
                    $expire_date = Carbon::parse($request->custom_expire_date);
                } else {
                    $expire_date = Carbon::now()->addMonth();
                }
                $change_package_info = Package::select('id', 'price', 'franchise_price')->where('id', $req_package[0])->first();

                if ($customer->customer_for == FRANCHISE_MANAGER) {
                    $customer_assing_package = ManagerAssignPackage::where(['package_id' => $change_package_info->id, 'manager_id' => auth()->user()->id])->first();
                    $bill = $customer_assing_package->manager_custom_price !== null ? $customer_assing_package->manager_custom_price : $change_package_info->franchise_price;
                    $bill = $bill - $customer->discount;
                } else {
                    $bill = $change_package_info->price - $customer->discount;
                }

                $manager = Manager::select('id', 'wallet', 'panel_balance', 'type')->where('id', $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id)->first();
                $manager->wallet = $manager->wallet + abs($request->wallet_cal_balance);
                if ($manager->type == FRANCHISE_MANAGER) $manager->panel_balance = $manager->panel_balance - abs($request->wallet_cal_balance);
                $manager->save();

                $customer->update([
                    'package_id'    => $change_package_info->id,
                    'wallet'        => intval($request->wallet_cal_balance) > 0 ? intval($request->wallet_cal_balance) : 0,
                    'expire_date'   => $expire_date,
                    'bill'          => $bill,
                    'status'        => CUSTOMER_ACTIVE,
                    'allow_grace'   => null
                ]);
                CustomerPackageChangeHistory::create(['customer_id' => $id, 'package_id' => $req_package[0], 'manager_id' => auth()->user()->id, 'expire_date' => $expire_date]);
                CustomerBalanceHistory::create(['customer_id' => $id, 'manager_id' => Auth::user()->id, 'balance' => abs($request->wallet_cal_balance), 'update_Reasons' => 'adjust Balance when package change']);
                if ($customer->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_PACKAGE_CHANGE, 'customer_id' => $customer->id, 'number' => $customer->phone]);
            }
            notify()->success('Package Change Successfully');
            DB::commit();
            return redirect()->route('user-view-user');
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            DB::rollBack();
            return back();
        }
    }
}
