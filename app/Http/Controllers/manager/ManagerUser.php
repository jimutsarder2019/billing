<?php

namespace App\Http\Controllers\Manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ManagerBalanceTransferHistory;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Manager as ManagerModel;
use App\Models\Customer as CustomerModel;
use App\Models\ManagerAssignPackage;
use App\Models\ManagerAssignSubZone;
use App\Models\ManagerAssignZone;
use App\Models\ManagerBalanceHistory;
use App\Models\Zone;
use App\Models\SubZone;
use App\Models\Mikrotik;
use App\Models\Role;
use App\Services\ConnectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission as ModelsPermission;
use Spatie\Permission\Models\Role as ModelsRole;

class ManagerUser extends Controller
{
    
    public function userChangePassword()
    {
        if (!auth()->user()->can('Managers View Profile')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        if(auth()->user()->id){
			$id = auth()->user()->id;
			try {
				$data = ManagerModel::with(
					'balanceSendHistory',
					'balanceSendHistory.receiver',
					'balanceReciveHistory',
					'balanceReciveHistory.sender',
					'mikrotik',
					'assignPackage',
					'assignPackage.package',
					'managerBalanceHistory',
					'invoices',
					'assingZones'
				)->find($id);
				return view('content.manager-user.user-profile', compact('data'));
			} catch (\Throwable $th) {
				//throw $th;
				notify()->warning($th->getMessage());
				return back();
			}
		}else{
			
		}
    }
	
	public function managerUserChangePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6',
            'password_confirmation' => 'required_with:password|same:password|min:6',
        ]);
        try {
            ManagerModel::find($id)->update(['password' => Hash::make($request->password)]);
            notify()->success('Password Change Successfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
	
	public function userChange()
    {
		if(auth()->user()->id){
			$mid = auth()->user()->id;
			$Manager = ManagerModel::find($mid);
			$id = $Manager->user_id;
			if (!auth()->user()->can('User Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
			$user = CustomerModel::where('id', $id)->first();
			$zones = Zone::get();
			$subzones = null;
			if ($user->zone_id) $subzones = SubZone::where('zone_id', $user->zone_id)->get();
			$packages = Package::where('price', '!=', null)->orWhere('price', '>', 0)->get();
			$mikrotiks = Mikrotik::get();
			return view('content.manager-user.edit-user', compact('user', 'zones', 'packages', 'mikrotiks', 'subzones'));
        }
    }
	
	public function userChangeProfile(Request $request, $id)
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
            if($user->save()){
				$mid = auth()->user()->id;
				$manager = ManagerModel::find($mid);
				$manager->name = $user->full_name;
				$manager->email = $user->email;
				//$manager->password = Hash::make($request->password);
				$manager->phone = $user->phone;
				$manager->address = $user->address;
				$manager->save();
			}
			
			
            if ($request->note) {
                CustomerEditHistory::create([
                    'customer_id' => $user->id,
                    'manager_id' => auth()->user()->id,
                    'subject' => 'Updated',
                    'note' => $request->note,
                ]);
            }
            notify()->success('Update Successfully');
            return redirect()->route('user-change');
        } catch (\Throwable $th) {
            return error_message($th);
        }
    }
	
	public function userDashboard()
    {
        try {
			$model = new Customer();
			$mid = auth()->user()->id;
			$Manager = ManagerModel::find($mid);
			$id = $Manager->user_id;
            $data =  $model->with('zone', 'sub_zone', 'package', 'mikrotik', 'connection_info', 'invoice', 'packageHistory', 'packageHistory.package')->find($id);
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
            return view('content.manager-user.user-dashboard', compact('data', 'mkt_data',));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
	
	public function userNotes()
    {
        try {
			$model = new Customer();
			$mid = auth()->user()->id;
			$Manager = ManagerModel::find($mid);
			$id = $Manager->user_id;
            $data =  $model->with('zone', 'sub_zone', 'package', 'mikrotik', 'connection_info', 'invoice', 'packageHistory', 'packageHistory.package')->find($id);
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
            return view('content.manager-user.user-notes', compact('data', 'mkt_data',));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
	
	public function userInvoice()
    {
        try {
			$model = new Customer();
			$mid = auth()->user()->id;
			$Manager = ManagerModel::find($mid);
			$id = $Manager->user_id;
            $data =  $model->with('zone', 'sub_zone', 'package', 'mikrotik', 'connection_info', 'invoice', 'packageHistory', 'packageHistory.package')->find($id);
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
            return view('content.manager-user.invoice', compact('data', 'mkt_data',));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
	
	public function userPackage()
    {
        try {
			$model = new Customer();
			$mid = auth()->user()->id;
			$Manager = ManagerModel::find($mid);
			$id = $Manager->user_id;
            $data =  $model->with('zone', 'sub_zone', 'package', 'mikrotik', 'connection_info', 'invoice', 'packageHistory', 'packageHistory.package')->find($id);
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
            return view('content.manager-user.package', compact('data', 'mkt_data',));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
	
	public function userTicket()
    {
        try {
			$model = new Customer();
			$mid = auth()->user()->id;
			$Manager = ManagerModel::find($mid);
			$id = $Manager->user_id;
            $data =  $model->with('zone', 'sub_zone', 'package', 'mikrotik', 'connection_info', 'invoice', 'packageHistory', 'packageHistory.package')->find($id);
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
            return view('content.manager-user.ticket', compact('data', 'mkt_data',));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
	
	public function userTicketHistory()
    {
        try {
			$model = new Customer();
			$mid = auth()->user()->id;
			$Manager = ManagerModel::find($mid);
			$id = $Manager->user_id;
            $data =  $model->with('zone', 'sub_zone', 'package', 'mikrotik', 'connection_info', 'invoice', 'packageHistory', 'packageHistory.package')->find($id);
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
            return view('content.manager-user.ticket-history', compact('data', 'mkt_data',));
        } catch (\Throwable $th) {
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
}
