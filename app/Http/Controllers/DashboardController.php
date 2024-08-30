<?php

namespace App\Http\Controllers;

use App\Http\Controllers\account\Account;
use App\Models\Customer;
use App\Models\CustomerGraceHistorys;
use App\Models\Invoice;
use App\Models\Package;
use App\Services\ConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public  function index(Request $request)
    {
        if (!auth()->user()->can('Dashboard')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $request['is_return'] = true;
            $accountcontroller = new Account();
            $monthly_report =  $accountcontroller->monthly_accounts($request);
            
			$data = null;
            $mkt_data = null;
            $mikrotik_id = '';
            $status = '';
            if ($request->has('username')) {
                $data = Customer::with('mikrotik')->where('username', $request->username)->first();
                if ($data) {
                    $invoices = Invoice::where('customer_id', $data->id)->latest()->first();

                    $connection = new ConnectionService($data->mikrotik->host, $data->mikrotik->username, $data->mikrotik->password, $data->mikrotik->port);
                    $res_query = $connection->check_milrotiok_user_status($data->username);

                    if (is_string($res_query)) return error_message($res_query);
                    $status = $res_query['status'];
                    $mikrotik_id = $data->mikrotik->id;
                    if (isset($res_query['query']) && is_array($res_query['query'])) {
                        foreach ($res_query['query'] as $key => $value) {
                            $mkt_data['caller-id'] = $value['caller-id'] ?? '';
                            $mkt_data['last-logged-out'] = $value['last-logged-out'] ?? '';
                            $mkt_data['uptime'] = $value['uptime'] ?? '';
                            $mkt_data['address'] = $value['address'] ?? '';
                        }
                    }
                }
            }
			
			
			
			
			
			
			
			
			
			if (auth()->user()->type == 'app_manager') {
                $today = Carbon::now()->format('d/m/Y H:i a');
                $today_expiring_customers = Customer::where(['status' => 'active'])->where('expire_date', '<=', $today)->get()->count();
                //return view('content.dashboard.app-manager-dashboard', compact('today_expiring_customers', 'monthly_report'));
            
			    return view('content.dashboard.app-manager-dashboard')->with([
					'data' => $data ?? [],
					'invoices' => $invoices ?? [],
					'caller_id' => isset($mkt_data['caller-id']) ? $mkt_data['caller-id'] : '',
					'last_logged_out' => isset($mkt_data['last-logged-out']) ? $mkt_data['last-logged-out'] : '',
					'uptime' => isset($mkt_data['uptime']) ? $mkt_data['uptime'] : '',
					'ip_address' => isset($mkt_data['address']) ? $mkt_data['address'] : '',
					'status' => $status,
					'mikrotik_id' => $mikrotik_id,
					'today_expiring_customers' => $today_expiring_customers,
					'monthly_report' => $monthly_report,
				]);
			
			} elseif (auth()->user()->type == 'franchise') {
                return view('content.dashboard.frinchise-dashboard', compact('monthly_report'));
            }
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    //mini_dashboard
    public function mini_dashboard(Request $request)
    {
        if (!auth()->user()->can('Mini Dashboard')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = null;
            $mkt_data = null;
            $mikrotik_id = '';
            $status = '';
            if ($request->has('username')) {
                $data = Customer::with('mikrotik')->where('username', $request->username)->first();
                if ($data) {
                    $invoices = Invoice::where('customer_id', $data->id)->latest()->first();

                    $connection = new ConnectionService($data->mikrotik->host, $data->mikrotik->username, $data->mikrotik->password, $data->mikrotik->port);
                    $res_query = $connection->check_milrotiok_user_status($data->username);

                    if (is_string($res_query)) return error_message($res_query);
                    $status = $res_query['status'];
                    $mikrotik_id = $data->mikrotik->id;
                    if (isset($res_query['query']) && is_array($res_query['query'])) {
                        foreach ($res_query['query'] as $key => $value) {
                            $mkt_data['caller-id'] = $value['caller-id'] ?? '';
                            $mkt_data['last-logged-out'] = $value['last-logged-out'] ?? '';
                            $mkt_data['uptime'] = $value['uptime'] ?? '';
                            $mkt_data['address'] = $value['address'] ?? '';
                        }
                    }
                }
            }

            return view('content.dashboard.mini-dashboard')->with([
                'data' => $data ?? [],
                'invoices' => $invoices ?? [],
                'caller_id' => isset($mkt_data['caller-id']) ? $mkt_data['caller-id'] : '',
                'last_logged_out' => isset($mkt_data['last-logged-out']) ? $mkt_data['last-logged-out'] : '',
                'uptime' => isset($mkt_data['uptime']) ? $mkt_data['uptime'] : '',
                'ip_address' => isset($mkt_data['address']) ? $mkt_data['address'] : '',
                'status' => $status,
                'mikrotik_id' => $mikrotik_id,
            ]);
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }


    // view customer to update data 
    function customerEditForSuperManager($id)
    {
        try {
            if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE) || !auth()->user()->can('Invoice Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $user  = Customer::find($id);
            $packages = Package::select('id', 'name', 'synonym', 'mikrotik_id', 'price')->where('mikrotik_id', $user->mikrotik_id)->get();
            return view('content.customer.edit-customer-for-superadmin', compact('user', 'packages'));
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }

    //  update customer for super admin data 
    function customerUpdateForSuperManager(Request $request, $id)
    {
        if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE) || !auth()->user()->can('Invoice Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $user  = Customer::find($id);
            $user->update([
                'registration_date' => $request->registration_date ?? $user->registration_date,
                'connection_date' => $request->connection_date ?? $user->connection_date,
                'expire_date' => $request->expire_date ?? $user->expire_date,
                'bill' => $request->bill ?? $user->bill,
                'status' => $request->status ?? $user->status,
                'wallet' => $request->wallet ?? $user->wallet,
                'package_id' => $request->package ?? $user->package_id,
                'purchase_package_id' => $request->purchase_package_id ?? $user->purchase_package_id,
                'allow_grace' => $request->allow_grace == 0 ? null : ($request->allow_grace ?? $user->allow_grace),
            ]);
            notify()->success('Updated Successfully');
            return redirect()->route('mini-dashboard');
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }
}
