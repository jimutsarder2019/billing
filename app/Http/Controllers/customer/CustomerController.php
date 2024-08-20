<?php

namespace App\Http\Controllers\customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Customer;
use App\Models\CustomerBalanceHistory;
use App\Models\CustomerEditHistory;
use App\Models\CustomerGraceHistorys;
use App\Models\CustomerPackageChangeHistory;
use App\Models\Invoice;
use App\Models\Manager;
use App\Models\ManagerAssignPackage;
use App\Models\ManagerBalanceHistory;
use App\Models\Mikrotik;
use App\Models\OltMac;
use App\Models\OltPonOnu;
use App\Models\Package;
use App\Models\PppUser;
use App\Models\SmsTemplates;
use App\Models\StaticData;
use App\Models\SubZone;
use App\Models\Zone;
use App\Services\ConnectionService;
use DateTime;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{

    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public $auth_user;
    public function __construct()
    {
        $this->model = new Customer();
        $this->modelName = "Customer";
        $this->routename = "customer-user.index";
        $this->table = "customers";
        $this->tamplate = "content.customer";
        $this->auth_user = auth()->user();
    }

    /**
     *👉 Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    ///new register cusotomer
    public function index(Request $request)
    {
        try {
            $mikrotik = Mikrotik::select('id', 'identity', 'host')->get();
            $users = $this->model->where('status', 'new_register')->latest()->paginate($request->item ?? 10);
            return view("$this->tamplate.list-new-reg-customer", compact('users', 'mikrotik'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    ///new register cusotomer
    public function customer_delete_permanently($id)
    {
        try {
            $this->model->find($id)->delete();
            notify()->success("Customer Delete Successfully");
            return back();
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    // save_customer_note
    function save_customer_note(Request $request)
    {
        try {
            if ($request->note_id) {
                // $data = CustomerEditHistory::where('id', $request->note_id)->first();

                $data = new CustomerEditHistory();
                $data->note = $request->customer_note;
                $data->subject = $request->subject ?? 'Updated';
                $data->customer_id = $request->customer_id;
                $data->manager_id = auth()->user()->id;
                $data->save();

                // $data->note = $request->customer_note;
                // $data->save();
            } else {
                $data = new CustomerEditHistory();
                $data->note = $request->customer_note;
                $data->subject = $request->subject ?? 'Created';
                $data->customer_id = $request->customer_id;
                $data->manager_id = auth()->user()->id;
                $data->save();
            }
            $data = CustomerEditHistory::with('manager')->where('id', $data->id)->first();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    // delete_customer_note
    function delete_customer_note($id, Request $request)
    {
        try {
            $data = CustomerEditHistory::find($id);
            if (!$data) error_message('data not found');
            $data->delete();
            notify()->success('Delete Successfully');
            return back();
        } catch (\Throwable $th) {
            return $th;
        }
    }


    /**
     *👉 Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    ///new register cusotomer
    public function bulk_renew_customer_expire_date(Request $request)
    {
        // dd($request->all());
        if (!auth()->user()->can('User Allow Grace')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'selected_for_grace_customers' => 'required|array|min:1',
        ]);
        DB::beginTransaction();
        try {
            $updated_customer = [];
            $non_updated_customer = [];
            foreach ($request->selected_for_grace_customers as $key => $item) {
                $expload_data = explode('|', $item);
                $c_id = $expload_data[0];
                // dd(format_ex_date($expload_data[1]));
                $customer = Customer::with('mikrotik', 'purchase_package')->find($c_id);
                if ($customer) {
                    if ($customer->bill <= $customer->wallet) {
                        $customer->update([
                            // 'allow_grace'   => null,
                            // 'expire_date'   => $expload_data[1],
                            // 'package_id'    => $customer->purchase_package_id,
                            // 'status'        => CUSTOMER_ACTIVE
                            // 'wallet'        => $customer->wallet-$customer->bill
                        ]);
                        $updated_customer[] = $customer;
                    } else {
                        $non_updated_customer[] = $customer;
                    }
                }
            }
            DB::commit();
            notify()->success("Bulk Expire Date Update Successfully");
            return back()->with(['updated_customer' => collect($updated_customer), 'non_updated_customer' => collect($non_updated_customer)]);
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    ///new register cusotomer
    public function customer_bulk_grace(Request $request)
    {
        if (!auth()->user()->can('User Allow Grace')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'allow_grace' => 'required|min:1',
            'selected_for_grace_customers' => 'required|array|min:1',
        ]);
        DB::beginTransaction();
        try {
            foreach ($request->selected_for_grace_customers as $key => $c_id) {
                $customer = Customer::with('mikrotik', 'purchase_package')->find($c_id);
                if ($customer) {
                    if ((empty($customer->allow_grace) | $customer->allow_grace == null && $customer->status == CUSTOMER_EXPIRE) | auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {

                        $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
                        $res =  $connection->activeDisconnectedUser($customer);
                        // expire date
                        $expire_date = Carbon::now()->addDay($request->allow_grace);
                        CustomerGraceHistorys::create([
                            'customer_id'   => $c_id,
                            'manager_id'    => auth()->user()->id,
                            'grace'         => $request->allow_grace,
                            'customer_new_expire_date' => $expire_date,
                            'grace_before_expire_date' => $customer->expire_date,
                        ]);
                        // 👉 update expire and grace
                        // 👉 update grace in database if super admin allow multiple time
                        $grace =  !empty($customer->allow_grace) | $customer->allow_grace !== null ?  $request->allow_grace + $customer->allow_grace : $request->allow_grace;
                        // 👉 crate historys
                        $customer->update([
                            'allow_grace'   => $grace,
                            'expire_date'   => $expire_date,
                            'package_id'    => $customer->purchase_package_id,
                            'status'        => CUSTOMER_ACTIVE
                        ]);
                    }
                }
            }
            DB::commit();
            notify()->success("Bulk Grace Added Successfully");
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function disable_customer(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $customer  =  $this->model->find($id);
            $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
            $disabled_user = $connection->mikrotik_user_change_status($customer->username, $request->status);
            if (gettype($disabled_user) == 'string') return error_message($disabled_user);
            $disconnected_user = $connection->disconnectConnectedUser($customer->username);
            if (gettype($disconnected_user) == 'string') return error_message($disconnected_user);
            $customer->update(['mikrotik_disabled' => $request->status]);
            DB::commit();
            notify()->success('Disconnect Successfully');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     *👉 Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    ///new register cusotomer
    public function expire_customer(Request $request)
    {
        try {
            $users = $this->model->with('mikrotik')
                ->where('status', 'expire')
                ->where(function ($q) {
                    if (auth()->user()->type == FRANCHISE_MANAGER) {
                        $q->where('manager_id', auth()->user()->id);
                    }
                })
                ->when($request->search_query, function ($q) use ($request) {
                    $searchQuery = '%' . $request->search_query . '%';
                    if (auth()->user()->type == FRANCHISE_MANAGER) {
                        return  $q = $q->where([
                            ['full_name', 'LIKE', '%' . $searchQuery . '%'],
                            ['manager_id', auth()->user()->id],
                            ['status', CUSTOMER_EXPIRE]
                        ])
                            ->orWhere([
                                ['username', 'LIKE', $searchQuery],
                                ['manager_id', auth()->user()->id],
                                ['status', CUSTOMER_EXPIRE]
                            ])
                            ->orWhere([
                                ['phone', 'LIKE', $searchQuery],
                                ['manager_id', auth()->user()->id],
                                ['status', CUSTOMER_EXPIRE]
                            ]);
                    } else {
                        return  $q = $q->where([
                            ['full_name', 'LIKE', '%' . $searchQuery . '%'],
                            ['status', CUSTOMER_EXPIRE]
                        ])
                            ->orWhere([
                                ['username', 'LIKE', $searchQuery],
                                ['status', CUSTOMER_EXPIRE]
                            ])
                            ->orWhere([
                                ['phone', 'LIKE', $searchQuery],
                                ['status', CUSTOMER_EXPIRE]
                            ]);
                    }
                })
                ->when($request->manager, function ($q) use ($request) {
                    return $q->where('manager_id', $request->manager);
                })
                ->orderBy('expire_date', 'desc')->paginate($request->item ?? 10);
            return view("$this->tamplate.expire-customer-list", compact('users'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function change_package_cal(Request $request, $id)
    {
        // return $request->all();
        try {
            $data = [];
            // 👉 find customer
            $customer = Customer::with('package')->find($id);
            // get last change package
            if ($request->expire_date && $request->expire_date !== 'null') {
                $cmr_exp_date = Carbon::parse($customer->expire_date)->endOfDay();
                // req_exp_date
                $req_day = Carbon::parse($request->expire_date)->endOfDay();
                // explode request
                $new_pack_info = explode('|', $request->new_pack);
                $grace_day = $customer->allow_grace !== null ? $customer->allow_grace : 0;
                // date different check
                $new_exp_date = Carbon::parse($request->expire_date)->endOfDay();
                $new_exp_date__formet = new DateTime($new_exp_date->format('Y-m-d'));
                $cmr_exp_date__formet = new DateTime($cmr_exp_date->format('Y-m-d'));
                $diff_cmr_ex_date_and_req_exp_date = $cmr_exp_date__formet->diff($new_exp_date__formet)->format("%r%a");
                $today = Carbon::now()->startOfDay(); // today

                $diff_cmr_exp_date_and_today =  $today->diff($cmr_exp_date__formet)->format("%r%a") + 1 - $grace_day;

                $diff_today_and_req_day = $req_day->diff($today)->format("%r%a") > 0 ? $req_day->diff($today)->format("%r%a") + 1 : $req_day->diff($today)->format("%r%a") - 1;

                $current_pack_per_day_bill =  ($customer->bill - $customer->discount) / $cmr_exp_date->daysInMonth;
                $new_pack_par_day_bill =  round(($new_pack_info[2] / $cmr_exp_date->daysInMonth), 2);
                $remaining_day_current_pack_price = round($current_pack_per_day_bill *   $diff_cmr_exp_date_and_today, 2);
                $remaining_day_new_pack_price = round($new_pack_par_day_bill *   $diff_cmr_exp_date_and_today, 2);

                $today_and_req_day_new_pack_price = abs(round($new_pack_par_day_bill * $diff_today_and_req_day, 2));

                $data['diff_cmr_exp_date_and_today'] = $diff_cmr_exp_date_and_today;
                $data['remaining_day_current_pack_price'] = $remaining_day_current_pack_price;
                $data['remaining_day_new_pack_price'] = $remaining_day_new_pack_price;
                $data['today_and_req_day_new_pack_price'] = $today_and_req_day_new_pack_price;

                $data['remaining_balance'] = ($customer->wallet + $remaining_day_current_pack_price);
                $data['wallet_after_change_packgae'] = round((($customer->wallet + $remaining_day_current_pack_price) - $today_and_req_day_new_pack_price), 2);
                return response()->json(['success' => true, 'data' => $data, 'code' => 200]);
            } else {
                if ($customer->status == CUSTOMER_ACTIVE) {
                    $exp_date = Carbon::parse($customer->expire_date);
                    $today = Carbon::now(); // today
                    if ($exp_date->day == $today->day) { // check if expire date 
                        $data['remaining_balance'] = $customer->bill - $customer->discount;
                        $data['remaining_day'] = 0 - $customer->allow_grace != null ? $customer->allow_grace : 0; // remaining_day 
                    } else {
                        $total_days_this_month = $today->daysInMonth; // total days of this month 
                        $diff_days = $exp_date->diffInDays($today); // diff days expire date from todays
                        $per_day =  round(($customer->bill - $customer->discount) / $total_days_this_month); //bill par day
                        $data['remaining_day'] =   ($diff_days - $customer->allow_grace ?? 0) + 1; // remaining_day 
                        $data['remaining_balance'] = $per_day * $diff_days; // remaining balance
                    }
                } else {
                    $data['remaining_day'] = 0 - ($customer->allow_grace != null ? $customer->allow_grace : 0); // remaining_day 
                    $data['remaining_balance'] = 0;
                }
                $new_pack_info = explode('|', $request->new_pack);
                $data['wallet_after_return'] = $customer->wallet + $data['remaining_balance'];
                $data['current_pkg_price_without_discount'] = ($new_pack_info[2] - $customer->discount);

                $data['wallet_after_change_packgae'] = $data['wallet_after_return'] - $data['current_pkg_price_without_discount'];
                return response()->json(['success' => true, 'data' => $data, 'code' => 200]);
            }
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'code' => $th->getCode()]);
        }
    }
    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_invoice($id)
    {
        try {
            $user =  Customer::with('package')->find($id);
            return view("content/user/collect-bill", compact('user'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_grace_page($id)
    {
        if (!auth()->user()->can('User Allow Grace')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $user =  Customer::with('package', 'customerGrace')->find($id);
            $grace = auth()->user()->grace_allowed;
            return view("content/user/customer-grace", compact('user', 'grace'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function mkt_pendingcustomer_assign_franchise(Request $request)
    {

        $request->validate([
            'franchise_manager' => 'required',
            'selected_customers' => 'required',
        ], [
            'selected_customers' => 'Plsease Select at last one customer',
        ]);
        DB::beginTransaction();
        try {
            foreach ($request->selected_customers as $key => $item) {
                $data =  PppUser::where('id', $item)->first();
                if (!$data) {
                    notify()->warning('pppUser Not found');
                    return back();
                }
                $package = Package::where(['name' => $data->profile, 'mikrotik_id' => $data->mikrotik_id])->first();
                if (!$package) {
                    notify()->warning('Package Not Found');
                    return back();
                }
                $manager_has_package = ManagerAssignPackage::with('package')->where(['package_id' => $package->id, 'manager_id' => $request->franchise_manager])->first();
                if ($manager_has_package) {
                    $data->update(['manager_id' => $request->franchise_manager, 'added_in_customers_table' => STATUS_FALSE]);
                } else {
                    notify()->warning('Customer package has no assign to manager');
                    return back();
                }
            }
            DB::commit();
            notify()->success('User Assigned Franchise Successfully');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
            //throw $th;
        }
    }
    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    function grace_user_list(Request $request)
    {
        if (!auth()->user()->can('Grace User')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $users  = Customer::with('package', 'mikrotik')
                ->when($request, function ($q) use ($request) {
                    if (auth()->user()->type == FRANCHISE_MANAGER) {
                        return $q->where('manager_id', auth()->user()->mikrotik_id);
                    }
                })
                ->when($request->orderBy, function ($q) use ($request) {
                    $q->orderBy('id', $request->orderBy);
                })
                ->when($request->search_query, function ($q) use ($request) {
                    $searchQuery = '%' . $request->search_query . '%';
                    return $q->where('full_name', 'LIKE', $searchQuery)
                        ->orWhere('username', 'LIKE', $searchQuery)
                        ->orWhere('phone', 'LIKE', $searchQuery);
                })
                ->whereNotNull('allow_grace')
                ->orderBy('expire_date', 'DESC')
                ->paginate($request->item ?? 10);
            return view("content.user.customer-grace-list", compact('users'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_allow_grace(Request $request, $id)
    {
        // dd($expire_date = Carbon::now()->addDay($request->allow_grace));
        $request->validate([
            'allow_grace' => 'required',
        ]);
        if (!auth()->user()->can('User Allow Grace')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        DB::beginTransaction();
        try {
            $customer = Customer::with('mikrotik', 'purchase_package')->find($id);
            if ((empty($customer->allow_grace) | $customer->allow_grace == null && $customer->status == CUSTOMER_EXPIRE) | auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
                $res =  $connection->activeDisconnectedUser($customer);
                // expire date
                $expire_date = Carbon::now()->addDay($request->allow_grace);
                CustomerGraceHistorys::create([
                    'customer_id'   => $id,
                    'manager_id'    => auth()->user()->id,
                    'grace'         => $request->allow_grace,
                    'customer_new_expire_date' => $expire_date,
                    'grace_before_expire_date' => $customer->expire_date,
                ]);
                // 👉 update expire and grace
                // 👉 update grace in database if super admin allow multiple time
                $grace =  !empty($customer->allow_grace) | $customer->allow_grace !== null ?  $request->allow_grace + $customer->allow_grace : $request->allow_grace;
                // 👉 crate historys
                $customer->update([
                    'allow_grace'   => $grace,
                    'expire_date'   => $expire_date,
                    'package_id'    => $customer->purchase_package_id,
                    'status'        => CUSTOMER_ACTIVE
                ]);
                DB::commit();
                notify()->success('Grace allow Successfully');
                return redirect()->route('user-view-user');
            } else {
                notify()->error('account already under grace');
                return back();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice_update(Request $request, $id)
    {
        $request->validate([
            'invoice_for' => 'required',
            'expire_date' => "required_if:invoice_for,monthly_bill,connection_fee",
            'amount' => 'required',
            'received_amount' => 'required',
            'paid_by' => 'required',
        ]);
        if ($request->invoice_for == 'add_balance') {
            $request->validate(['received_amount' => "required|same:amount"]);
        }
        DB::beginTransaction();
        try {
            $status  = $request->amount > $request->received_amount ? 'due' : ($request->amount < $request->received_amount ? 'over_paid' : 'paid');
            Invoice::create([
                'customer_id' => $id,
                'invoice_no' => "INV-{$request->user_id}-" . date('m-d-Hms-Y'),
                'invoice_for' => $request->invoice_for,
                'expire_date' => $request->expire_date,
                'amount' => $request->amount,
                'received_amount' => $request->received_amount,
                // 'due_amount' => $status == 'due' ? $request->amount - $request->received_amount : 00,
                // 'advanced_amount' => $status == 'over_paid' ? $request->received_amount - $request->amount : 00,
                'status' => $status,
                'invoice_type' => INVOICE_TYPE_INCOME,
                'manager_for' => auth()->user()->type,
                'paid_by' => $request->paid_by,
                'transaction_id' => $request->transaction_id,
                'manager_id' => Auth::user()->id,
            ]);
            if ($request->invoice_for == 'add_balance') {
                Customer::find($id)->increment('wallet', floatval($request->received_amount));
                CustomerBalanceHistory::create(['customer_id' => $id, 'manager_id' => Auth::user()->id, 'balance' => $request->received_amount, 'update_Reasons' => $request->invoice_for]);
            }
            // $data = SendSingleMessage(['template_type' => 'invoice_create', 'number' => CustomerModel::where('id', $request->user_id)->first()->phone]);
            // if ($data == false) {
            //     notify()->success("Invoice Create Successfully");
            // }
            notify()->success("Invoice Create Successfully");
            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $zones = Zone::get();
        $subzones = SubZone::get();
        $packages = Package::get();
        return view("content.customer.customer-new-registration", compact('zones', 'subzones', 'packages'));
    }

    // new registration customer 
    /**
     *👉 Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => "required|unique:$this->table,full_name",
            'phone' => 'required',
        ]);
        try {
            $this->model->create([
                'full_name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'national_id' => $request->national_id,
                'phone' => $request->phone,
                'date_of_birth' => $request->dob,
                'father_name' => $request->f_name,
                'mother_name' => $request->m_name,
                'address' => $request->address,
                'zone_id' => $request->zone_id,
                'sub_zone_id' => $request->sub_zone_id,
                'registration_date' => $request->reg_date,
                'connection_date' => $request->conn_date,
                'package_id' => $request->package_id,
                'purchase_package_id' => $request->package_id,
                'bill' => $request->bill,
                'discount' => $request->discount,
                'customer_for' => auth()->user()->type,
                'manager_id' => auth()->user()->id,
                'status' => 'new_register'
            ]);
            notify()->success("$this->modelName Create Successfully");
            return redirect()->route("$this->routename");
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    // new registration customer 
    /**
     * 👉 Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setNewUserInMikrotik(Request $request, $id)
    {
        $request->validate([
            'mikrotik' => 'required',
            'mikrotik_user_name' => 'required',
            'package_id' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $user = $this->model->where('id', $id)->first();
            $user->mikrotik_id = $request->mikrotik;
            $user->username = $request->mikrotik_user_name;
            $user->password = $request->password;
            $user->package_id = $request->package_id;
            $user->bill = $request->amount;
            $user->bill = $request->amount;
            $user->status = CUSTOMER_PENDING;
            $user->save();
            DB::commit();
            notify()->success('Connection Info Update Successfully');
            return redirect()->route('user-view-user');
        } catch (Exception $exception) {
            DB::rollBack();
            info($exception->getMessage());
            dd($exception);
            // return error_message('Something went wrong!', $exception->getMessage(), $exception->getCode());
            // return 0;
        }
    }
    // mikrotik online user
    public function mikrotik_online_user(Request $request)
    {
        try {
            if (auth()->user()->type == 'franchise') {
                $request['mikrotik_id'] = auth()->user()->mikrotik_id;
            }

            $users = array();
            $offline_user_list = array();
            $total_users = 0;
            $total_online = 0;
            $total_offline_users = 0;
            if ($request->mikrotik_id) {
                $mikrotik =  Mikrotik::where('id', $request->mikrotik_id)->first();
                if (!$mikrotik) {
                    notify()->warning('Mikrotik Not Found');
                    return back();
                }
                // 👉 get and check all online user
                $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
                // 👉 call mikrotik online users 
                $query_all_active_online_user =  $connection->getallmikrotikOnlineusers();
                /** @var array $offline_user */
                $offline_user =  $connection->count_mikrotik_offlen_user();
                $total_users = count($offline_user);
                $total_online = count($query_all_active_online_user);
                $total_offline_users = count($offline_user) - $total_online;
                foreach ($offline_user as $offline_item) {
                    $found = false;
                    foreach ($query_all_active_online_user as $online_user) {
                        if (isset($online_user['name']) && $online_user['name'] == $offline_item['name']) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        // 👉 check and find from database if exists customer
                        $customer = Customer::select('status', 'username')->where('username', $offline_item['name'])->first();
                        // 👉 assign status in list items

                        if (auth()->user()->type == 'franchise') {
                            $check_franchise_custpmer = Customer::select('status', 'username', 'manager_id')
                                ->where(['username' => $offline_item['name'], 'manager_id' => auth()->user()->id])->first();
                            if ($check_franchise_custpmer) {
                                $offline_item['billing_status'] = $customer ? $customer->status : '';
                                $offline_user_list[] = $offline_item;
                            }
                        } else {
                            $offline_item['billing_status'] = $customer ? $customer->status : '';
                            $offline_user_list[] = $offline_item;
                        }
                    }
                }

                // dd($offline_user_list);

                foreach ($query_all_active_online_user as $index => $sItem) {
                    //👉 check franchise user
                    if (auth()->user()->type == 'franchise') {
                        $checkuser = Customer::select('id', 'username', 'manager_id')->where(['username' => $sItem['name'], 'manager_id' => auth()->user()->id])->first();
                        if ($checkuser) {
                            $users[] = $sItem;
                        }
                    } else {
                        $users[] = $sItem;
                    }
                    continue;
                }

                // 👉 Pagination

            }
            // 👉 Set the path for the pagination links
            $mkt_id = $request->mikrotik_id;
            return view('content.user.mikrotik-online-user-list', compact(
                'users',
                'mkt_id',
                'total_online',
                'total_offline_users',
                'total_users',
                'offline_user_list',
            ));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /* 
    * 👉 getallmikrotikusers
    * Request
    */
    public function mikrotik_online_disconnect(Request $request, $id)
    {
        $mkitem = Mikrotik::find($id);
        try {
            $connection = new ConnectionService($mkitem->host, $mkitem->username, $mkitem->password, $mkitem->port);
            $connection->disconnectConnectedUser($request->name);
            notify()->success('User Disconnect Successfully');
            return back();
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = $this->model->with('zone', 'sub_zone', 'package', 'mikrotik', 'connection_info', 'invoice', 'packageHistory', 'packageHistory.package')->find($id);
            try {
                $connection = new ConnectionService($data->mikrotik->host, $data->mikrotik->username, $data->mikrotik->password, $data->mikrotik->port);
                $res_query = $connection->check_milrotiok_user_status($data->username);
                if (is_string($res_query)) return error_message($res_query);
                $status = $res_query['status'];
            } catch (\Throwable $th) {
                $status = null;
            }
            $mikrotik_id = $data->mikrotik->id;
            $mkt_data = [];
            if (isset($res_query['query']) && is_array($res_query['query'])) {
                foreach ($res_query['query'] as $key => $value) {
                    $mkt_data['caller-id'] = $value['caller-id'] ?? '';
                    $mkt_data['last-logged-out'] = $value['last-logged-out'] ?? '';
                    $mkt_data['uptime'] = $value['uptime'] ?? '';
                    $mkt_data['address'] = $value['address'] ?? '';
                }
            }
            $mkt_data = new Collection($mkt_data);
            if (isset($mkt_data['caller-id'])) {
                $data->mac_address = $mkt_data['caller-id'];
                $data->save();
            }


            return view('content.customer.view-single-customer', compact('data', 'mkt_data',));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     *👉 Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $data = $this->model->find($id);
            if (!$data) return abort(404);
            return view("$this->tamplate.addEdit", compact('data'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     *👉 Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function customer_delete($id)
    {
        try {
            $data = $this->model->find($id);
            if (!$data) return notify()->warning('data not found');
            $data->delete();
            notify()->success("$this->modelName Delete Successfully");
            return back();
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * 👉 Update the User expire date
     * @param  int  $id
     */
    public function user_change_expire_date($id)
    {
        // if (!auth()->user()->can('User Change expire date')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $user = Customer::with('package')->find($id);
        if (!$user) return error_message('Data not found');
        return view('content.user.user-change-expire-date', compact('user'));
    }
    /**
     * 👉 Update the User expire date
     * @param  int  $id
     */
    public function user_change_expire_date_put(Request $request, $id)
    {
        $request->validate([
            'amount'                => 'required|min:1',
            'custom_expire_date'    => 'required',
            'paid_by'               => 'required',
        ]);
        $auth_user = auth()->user();
        // if (!auth()->user()->can('User Change expire date')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        DB::beginTransaction();
        try {
            $customer = Customer::with('select_manager', 'mikrotik')->find($id);
            if (!$customer) return error_message('Data not found');
            if ($request->amount > 0) {
                if ($customer->customer_for == FRANCHISE_MANAGER && $customer->select_manager) {
                    if ($customer->select_manager->panel_balance < $request->amount) return error_message('no enough balance');
                    $franchise_manager = Manager::where('id', $customer->select_manager->id)->first();
                    $franchise_manager->panel_balance = $franchise_manager->panel_balance - $request->amount;
                    $franchise_manager->save();
                }
                $manager = Manager::where('id', $auth_user->id)->first();
                $manager->wallet = $manager->wallet + $request->amount;
                $manager->save();
                $customer->expire_date = format_ex_date($request->custom_expire_date);
                $customer->save();
                if ($customer->status == CUSTOMER_EXPIRE) {
                    // 👉 connect with mikrotik
                    $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
                    $res_query = $connection->changeCustomerPackage($customer, $request);
                    if (gettype($res_query) == 'string') return error_message($res_query);
                    $customer->update([
                        'package_id'    => $customer->purchase_package_id,
                        'status'        => CUSTOMER_ACTIVE,
                        'allow_grace'   => null,
                    ]);
                }
                // 👉create and paid invoice
                $invoice = Invoice::create([
                    'customer_id'    => $id,
                    'invoice_no'     => "INV-{$id}-" . date('m-d-Hms-Y'),
                    'invoice_for'    => INVOICE_CUSTOMER_MONTHLY_BILL,
                    'package_id'     => $customer->package_id,
                    'zone_id'        => $customer->zone_id,
                    'sub_zone_id'    => $customer->sub_zone_id ?? null,
                    'amount'         => $request->amount,
                    'amount'         => $request->amount,
                    'paid_by'        => $request->paid_by,
                    'transaction_id' => $request->transaction_id,
                    'status'        => STATUS_PAID,
                    'invoice_type'  => INVOICE_TYPE_INCOME,
                    'manager_for'   => $customer->customer_for,
                    'comment'       => "Change Customer Expire Date $customer->expire_date",
                    'manager_id'    => $auth_user->id,
                ]);
                // send sms
                if ($customer->customer_for == APP_MANAGER) {
                    if ($request->is_send_welcome_sms) {
                        $sms_account_tamp = SmsTemplates::select('id', 'name', 'type')->where('type', SEND_SMS_UPDATE_EXPIRE_DATE)->first();
                        if (!$sms_account_tamp) return error_message('sms tamplate Not Found');
                        // 👉 send welcome Msg when custoner active account
                        SendSingleMessage([
                            'template_type' => SEND_SMS_UPDATE_EXPIRE_DATE,
                            'number'        => $customer->phone,
                            'customer_id'   => $customer->id,
                            'invoice'       => $invoice
                        ]);
                    }
                }
            } else {
                return error_message('Something want Wrong :) ');
                // $customer->expire_date = format_ex_date($request->custom_expire_date);
                // $customer->save();
            }
            DB::commit();
            notify()->success('Updated Successfully');
            return redirect()->route('user-view-user');
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            dd($th);
            return back();
        }
    }
    /**
     * 👉 Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function desabled_multiple_customer(Request $request)
    {
        try {
            foreach ($request->customers_id as $key => $value) {
                $this->disable_for_multiple_customer($value, $request->status);
            }
            notify()->success($this->modelName . $request->status == 0 ? 'Enabled' : 'Disabled' . " Successfully");
            return back();
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    // 👉 disable for multiple customer
    public function disable_for_multiple_customer($id, $status)
    {
        DB::beginTransaction();
        try {
            $customer  =  $this->model->find($id);
            $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
            $disabled_user = $connection->mikrotik_user_change_status($customer->username, $status);
            if (gettype($disabled_user) == 'string') return error_message($disabled_user);
            $disconnected_user = $connection->disconnectConnectedUser($customer->username);
            if (gettype($disconnected_user) == 'string') return error_message($disconnected_user);
            $customer->update(['mikrotik_disabled' => $status]);
            DB::commit();
            notify()->success('Cutomer Disabled successfully');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     * 👉 Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function customer_suspended($id)
    {
        try {
            $customer = Customer::with('mikrotik', 'manager')->find($id);
            $last_pack_info = CustomerPackageChangeHistory::with('package')->where('customer_id', $id)->latest()->first();
            if ($last_pack_info) {
                $create_date = Carbon::parse($last_pack_info->created_at);
                $create_date =  Carbon::parse($create_date);
                $expire_date = Carbon::parse($last_pack_info->expire_date);
                $packdayDiff = $expire_date->diffInDays($create_date);
                $today = Carbon::now();
                // 👉 different from today
                $diffFromToday = $create_date->diffInDays($today);
                $pack_price_per_day = $customer->bill / $packdayDiff;
                $current_unused_price = (($packdayDiff - $diffFromToday) * $pack_price_per_day) - $customer->discount;
            } else {
                $current_unused_price = 0;
            }

            // 👉 user expire in mikrotik 
            $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
            $default_expire_package = AdminSetting::where('slug', 'disconnect_package')->first();
            if (!$default_expire_package) return error_message('Disconnect package not found');
            $expired_package = Package::where('name', $default_expire_package->value)->where('mikrotik_id', $customer->mikrotik_id)->first();
            if (!$expired_package) return error_message('Disconnect package not found');
            $connection->disconnectUserProfile($customer->id, $customer->username, $expired_package->name);
            // 👉 check if customer has wallet balance then add invoice and franchise panel balence
            if ($customer->wallet > 0) $current_unused_price = $current_unused_price += $customer->wallet;
            if ($current_unused_price > 0) {
                // 👉 crate new invoice 
                $inv = Invoice::create([
                    'customer_id'       => $customer->id,
                    'invoice_no'        => "INV-$customer->id" . date('m-d-Hms-Y'),
                    'invoice_for'       => INVOICE_DELETE_CUSTOMER,
                    'amount'            => $current_unused_price,
                    'received_amount'   => $current_unused_price,
                    'paid_by'           => 'cash',
                    'status'            => STATUS_PAID,
                    'manager_for'       => $customer->manager->type,
                    'manager_id'        => $customer->manager_id,
                    'invoice_type'      => INVOICE_TYPE_EXPENCE,
                    'comment'           => 'Customer Delete',
                ]);
                // 👉 FRANCHISE_MANAGER update panel balance for customer unused package price               
                if ($customer->manager->type == FRANCHISE_MANAGER) {
                    Manager::where('id', $customer->manager_id)->increment('panel_balance', $current_unused_price);
                    ManagerBalanceHistory::create([
                        'manager_id' => $customer->manager_id,
                        'app_manager_id' => auth()->user()->id,
                        'balance' => $current_unused_price,
                        'franchise_panel_balance' => $customer->manager->type == FRANCHISE_MANAGER ? $customer->manager->panel_balance : 00,
                        'history_for' => INVOICE_DELETE_CUSTOMER,
                        'status' => STATUS_ACCEPTED,
                        'sign' => '+',
                        'invoice_id' => $inv->id
                    ]);
                }
            }
            // 👉 update customer details 
            $customer->update([
                'package_id' => $expired_package->id,
                'purchase_package_id' => $customer->package_id,
                'status' => CUSTOMER_DELETE,
                'wallet' => 00,
            ]);
            // 👉 send sms if customer for not equal franchise manager
            if ($customer->customer_for !== FRANCHISE_MANAGER) SendSingleMessage(['template_type' => 'account_expire', 'number' => $customer->phone, 'customer_id' => $customer->id]);
            notify()->success('Customer Delete Successfully');
            DB::commit();
            return back();
        } catch (\Throwable $th) {
            //throw $th;    
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // 👉 confirm_payment
    public function confirm_payment($id)
    {
        try {
            $user = Customer::find($id);
            return view('content.user.confirm-customer', compact('user'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    // 👉 mikrotik_import_users
    public function mikrotik_import_users(Request $request)
    {
        $mikroti_id = $request->mikrotik_id;
        try {
            $mikrotik_users = PppUser::with('manager')
                ->when(auth()->user(), function ($q) use ($request) {
                    if (auth()->user()->type == FRANCHISE_MANAGER) {
                        return  $q->where('mikrotik_id', auth()->user()->mikrotik_id);
                    } else {
                        return  $q->where('mikrotik_id', $request->mikrotik_id);
                    }
                })
                ->when(auth()->user(), function ($q) {
                    if (auth()->user()->type == FRANCHISE_MANAGER) {
                        return $q->where('manager_id', auth()->user()->id);
                    }
                })
                ->paginate($request->item ?? 10);
            return view('content.user.mikrotik-import-user', compact('mikrotik_users', 'mikroti_id'));
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }
}
