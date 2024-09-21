<?php

namespace App\Http\Controllers\manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ManagerBalanceTransferHistory;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Manager as ManagerModel;
use App\Models\ManagerAssignPackage;
use App\Models\ManagerAssignSubZone;
use App\Models\ManagerAssignZone;
use App\Models\ManagerBalanceHistory;
use App\Models\Zone;
use App\Models\SubZone;
use App\Models\Mikrotik;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Permission as ModelsPermission;
use Spatie\Permission\Models\Role as ModelsRole;

class Manager extends Controller
{
    //  =========== list Managers ===========
    public function listManagers(Request $request)
    {
        if (!auth()->user()->can('Managers View')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $type = $request->type;
        $managers = ManagerModel::with('assingZones', 'assingZones.zone', 'customers')->when($request->type, function ($q) use ($request) {
            return $q->where('type', $request->type);
        })->latest()->paginate($request->item ?? 10);
        $zones = Zone::get();
        $sub_zones = SubZone::get();
        $mikrotiks = Mikrotik::get();
        $roles = Role::get();
        return view('content.manager.manager-list', compact('managers', 'zones', 'sub_zones', 'mikrotiks', 'roles', 'type'));
    }

    // =======manager_create=======
    public function manager_create(Request $request)
    {
        if (!auth()->user()->can('Managers Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $zones = Zone::get();
        $sub_zones = SubZone::get();
        $mikrotiks = Mikrotik::get();
        $roles = Role::get();
        return view('content.manager.add-manager', compact('zones', 'sub_zones', 'mikrotiks', 'roles'));
    }
    // ========= store Manager =========
    public function storeManager(Request $request)
    {
        if (!auth()->user()->can('Managers Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'type'          => 'required',
            'name'          => 'required',
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:' . ManagerModel::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'         => 'required',
            'zones'         => 'required|array',
            'mikrotik_id'   => 'required',
            'address'       => 'required',
        ]);
        DB::beginTransaction();
        try {
            $manager = ManagerModel::create([
                'type' => $request->type,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'sub_zone_id' => $request->subzone_id,
                'mikrotik_id' => $request->mikrotik_id,
                'package_id' => $request->package_id,
                'address' => $request->address,
                'grace_allowed' => $request->grace,
            ]);

            //Store Assigned zone
            if ($request->has('zones') && count($request->zones) > 0) {
                foreach ($request->zones as $key => $value) {
                    ManagerAssignZone::create(['manager_id' => $manager->id, 'zone_id' => $value]);
                }
            }
            //Store Assigned zone
            if ($request->has('sub_zones') && count($request->sub_zones) > 0) {
                foreach ($request->zones as $key => $value) {
                    ManagerAssignSubZone::create(['manager_id' => $manager->id, 'subzone_id' => $value]);
                }
            }

            //Store Assigned package
            if ($request->has('package') && count($request->package) > 0) {
                foreach ($request->package as $key => $value) {
                    if ($request->has('price_editable')) {
                        if (in_array($value, $request->price_editable)) {
                            ManagerAssignPackage::create(['manager_id' => $manager->id, 'package_id' => $value, 'is_manager_can_add_custom_package_price' => STATUS_TRUE]);
                        } else {
                            ManagerAssignPackage::create(['manager_id' => $manager->id, 'package_id' => $value]);
                        }
                    }
                }
            }


            if ($request->prefix == 'on') {
                $prefix = true;
                $request->validate([
                    'prefix_text' => 'required'
                ]);
                $manager->prefix = $prefix;
                $manager->prefix_text = $request->prefix_text;
                $manager->save();
            } else {
                $prefix = false;
                $manager->prefix = $prefix;
                $manager->save();
            }

            DB::commit();
            activity()->event('Create Manager')->log("Create manager New Manager. Name: $manager->name");
            notify()->success('Created Successfully');
            return redirect()->route('managers-manager-list');
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
    function franchise_panel_balance_invoice(Request $request)
    {
        $data = Invoice::with('franchise_manager', 'manager')->where('invoice_for', INVOICE_MANAGER_ADD_PANEL_BALANCE)->latest()->paginate($request->item ?? 10);
        return view('content.manager.franchise_panel_balance_invoice', compact('data'));
    }

    //  ======== franchise add custom package price ========
    public function editManager($id)
    {
        if (!auth()->user()->can('Managers Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $manager = ManagerModel::with('assingZones', 'subzones')->find($id);
            $zones = Zone::get();
            $sub_zones = SubZone::get();
            $mikrotiks = Mikrotik::get();
            $roles = Role::get();
            activity()->event('Edit Manager')->log("Edit manager. Manager Name: $manager->name");
            return view('content.manager.edit-manager', compact('manager', 'zones', 'sub_zones', 'mikrotiks', 'roles'));
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }


    // ======  Update Manager=======
    public function updateManager(Request $request, $id)
    {
        if (!auth()->user()->can('Managers Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
        ]);

        try {
            $manager = ManagerModel::find($id);
            $manager->type = $request->type ?? $manager->type;
            $manager->name = $request->name;
            $manager->email = $request->email;
            $manager->password = $request->password ? Hash::make($request->password) : $manager->password;
            $manager->phone = $request->phone;
            $manager->zone_id = $request->zone_id;
            $manager->sub_zone_id = $request->subzone_id;
            $manager->mikrotik_id = $request->mikrotik_id ?? $manager->mikrotik_id;
            $manager->address = $request->address;
            $manager->grace_allowed = $request->grace;

            if ($request->prefix == 'on') {
                $prefix = true;
                $request->validate([
                    'prefix_text' => 'required'
                ]);
                $manager->prefix = $prefix;
                $manager->prefix_text = $request->prefix_text;
                $manager->save();
            } else {
                $prefix = false;
                $manager->prefix = $prefix;
                $manager->save();
            }

            // update manager assign zone
            if ($request->has('zones') && count($request->zones) > 0) {
                ManagerAssignZone::where('manager_id', $manager->id)->delete();
                foreach ($request->zones as $key => $value) {
                    ManagerAssignZone::create(['manager_id' => $manager->id, 'zone_id' => $value]);
                }
            }
            // update manager assign sub_zone
            if ($request->has('subzones') && count($request->subzones) > 0) {
                ManagerAssignSubZone::where('manager_id', $manager->id)->delete();
                foreach ($request->subzones as $key => $value) {
                    ManagerAssignSubZone::create(['manager_id' => $manager->id, 'subzone_id' => $value]);
                }
            }
            // update franchise manager assign package
            if ($request->has('package') && count($request->package) > 0) {
                ManagerAssignPackage::where('manager_id', $manager->id)->delete();
                foreach ($request->package as $key => $value) {
                    if ($request->has('price_editable')) {
                        if (in_array($value, $request->price_editable)) {
                            ManagerAssignPackage::create(['manager_id' => $manager->id, 'package_id' => $value, 'is_manager_can_add_custom_package_price' => STATUS_TRUE]);
                        } else {
                            ManagerAssignPackage::create(['manager_id' => $manager->id, 'package_id' => $value]);
                        }
                    } else {
                        ManagerAssignPackage::create(['manager_id' => $manager->id, 'package_id' => $value]);
                    }
                }
            }

            notify()->success('Update Successfully');
            return redirect()->route('managers-manager-list');
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }

    //========delete manager========
    public function manager_delete($id)
    {
        if (!auth()->user()->can('Managers Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            if (!auth()->user()->can('Managers Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data = ManagerModel::with('customers', 'balanceReciveHistory', 'balanceSendHistory', 'assingZones')->find($id);
            if (!$data) return abort(404);
            if ($data->customers->count() > 0 || $data->balanceReciveHistory->count() > 0 || $data->balanceSendHistory->count() > 0 || $data->assingZones->count() > 0) {
                notify()->warning('Please Delete Relevant Data like assign zone, customers or balance taransfer');
                return back();
            }
            $data->delete();
            notify()->success('Mnager Delete Successfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }

    //  ======== Roles List========
    public function listRoles()
    {
        if (!auth()->user()->can('Role')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $permission = ModelsPermission::select('id', 'name', 'group_name')->get();
        // dd($permission);
        $roles = Role::all();
        return view('content.role.role-list', compact('roles', 'permission'));
    }
    //  ======== Store Role========

    public function storeRole(Request $request)
    {
        if (!auth()->user()->can('Role Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:' . Role::class]
        ]);
        try {
            Role::create([
                'name' => $request->name,
                'guard_name' => Auth::getDefaultDriver()
            ]);
            notify()->success("Role Create Successfully");
            return redirect()->route('managers-role-list');
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    //  ========update Role========
    public function updateRole(Request $request, $id)
    {
        if (!auth()->user()->can('Role Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $validator = Validator::make($request->all(), [
            'name' => "required|string|max:255|unique:roles,name,$id",
        ]);
        if ($validator->fails()) return error_message($validator->getMessageBag());
        $role = Role::find($id);
        $role->name = $request->name;
        $role->update();
        notify()->success("Update Successfully");
        return redirect()->route('managers-role-list');
    }
    //delete role
    public function role_delete($id)
    {
        try {
            if (!auth()->user()->can('Managers Assign Role')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $model_has_role =  DB::table('model_has_roles')->where('role_id', $id)->get();
            $role_has_permissions =  DB::table('role_has_permissions')->where('role_id', $id)->get();
            if ($model_has_role->count() > 0 || $role_has_permissions->count() > 0) {
                notify()->warning('Please Delete assigned role and permissions');
                return back();
            } else {
                $data = Role::find($id);
                if (!$data) return abort(404);
                if ($data->name == SUPER_ADMIN_ROLE) {
                    notify()->warning("you can not delete Super admin role");
                    return back();
                }
                $data->delete();
                notify()->success('Role Delete Successfully');
                return redirect()->route('managers-role-list');
            }
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    //add role to manager 
    public function addRoleToManager(Request $request, $id)
    {
        $request->validate(['role_id' => 'required']);
        try {
            if (!auth()->user()->can('Managers Assign Role')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $manager = ManagerModel::find($id);
            $role = ModelsRole::where('id', $request->role_id)->first();
            $manager->syncRoles([]);
            $manager->assignRole($role->name);
            notify()->success('Role Assign Successfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // managers_add_balance
    public function managers_add_balance(Request $request, $id)
    {
        if ($request->amount < $request->received_amount) return error_message('received amount must be same or smaller then main amount');
        if (!auth()->user()->can('Managers Add Custom Balance')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        DB::beginTransaction();
        try {
            $data = ManagerModel::find($id);
            $status  = $request->amount > $request->received_amount ? 'due' : ($request->amount < $request->received_amount ? 'over_paid' : 'paid');

            /* @importent note
            * note: hare this invoice fo app_managers is show income list  we can get this app_manager filed is invoice_type=INVOICE_TYPE_INCOME manager_for == APP_MANAGER
            * note2: on the other side for franchise manager this invoice is show expense list to get by manager_id and  invoice_for  == INVOICE_MANAGER_ADD_PANEL_BALANCE
            */
            $inv = Invoice::create([
                'manager_id' =>  auth()->user()->id,
                'franchise_manager_id' => $data->id,
                'invoice_no' => "INV-{$data->id}-" . date('m-d-Hms-Y'),
                'invoice_for' => INVOICE_MANAGER_ADD_PANEL_BALANCE,
                'amount' => $request->amount,
                'received_amount' => $request->received_amount,
                'due_amount' => $status == 'due' ? $request->amount - $request->received_amount : 00,
                'paid_by' => 'cash',
                'transaction_id' => $request->transaction_id,
                'status' => $status,
                'manager_for' => APP_MANAGER,
                'invoice_type' => INVOICE_TYPE_INCOME,
                'comment' => 'Add Manager panel Balance',
            ]);

            ManagerModel::where('id', auth()->user()->id)->increment('wallet', $request->received_amount);
            ManagerBalanceHistory::create([
                'manager_id' => $data->id,
                'app_manager_id' => auth()->user()->id,
                'balance' => $request->amount,
                'franchise_panel_balance' => $data->panel_balance,
                'history_for' => INVOICE_MANAGER_ADD_PANEL_BALANCE,
                'sign' => '-',
                'invoice_id' => $inv->id
            ]);
            //add app manager wallet
            notify()->success("Balance Update Successfully");
            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // manager_balance_transfer
    public function manager_balance_transfer_get($id)
    {
        try {
            if (!auth()->user()->can('Managers Add Custom Balance')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $manager = ManagerModel::find($id);
            $allmanagers = ManagerModel::get();
            return view('content.manager.balance-transfer', compact('manager', 'allmanagers'));
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // manager store balance_transfer
    public function manager_balance_transfer_put(Request $request, $id)
    {
        if (!auth()->user()->can('Managers Balance Transfer')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            ManagerBalanceTransferHistory::create([
                'sender_id' => $id,
                'reciver_id' => $request->reciver_id,
                'amount' => $request->amount,
            ]);
            notify()->success("Balance Transefer Successfully");
            return redirect()->route('managers-manager-list');
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // manager store balance_transfer
    public function accept_transfer_balance(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = ManagerBalanceTransferHistory::find($id);
            $recived_amount = $request->has('recived_amount') ? $request->recived_amount : $data->amount;
            $data->recived_amount = $recived_amount;
            $data->status = $recived_amount == $data->amount ? 'accepted' : 'custom_accepted';
            $data->notification_status = STATUS_TRUE;
            $data->save();
            ManagerModel::where('id', $data->sender_id)->decrement('wallet', floatval($recived_amount));
            ManagerModel::where('id', $data->reciver_id)->increment('wallet', floatval($recived_amount));
            notify()->success("Transefer Accept Successfully");
            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // manager store balance_transfer
    public function seen_transfer_balance_notification($id)
    {
        try {
            $data = ManagerBalanceTransferHistory::find($id);
            $data->notification_status =  STATUS_FALSE;
            $data->save();
            notify()->success("Notification Seen Successfully");
            return back();
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // manager store balance_transfer
    public function view_transfer_balance($id)
    {
        try {
            $data = ManagerBalanceTransferHistory::with('receiver', 'sender')->find($id);
            return view('content.manager.view-transfer-balance', compact('data'));
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }

    // manager store balance_transfer
    public function rejacte_managers_balance_transfer($id)
    {
        if (!auth()->user()->can('Managers Balance Transfer')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            DB::beginTransaction();
            try {
                $data = ManagerBalanceTransferHistory::find($id);
                $data->status = 'rejected';
                $data->notification_status = STATUS_TRUE;
                $data->save();
                notify()->success("Rejected Account Transfer");
                DB::commit();
                return redirect()->route('managers-manager-list');
            } catch (\Throwable $th) {
                DB::rollBack();
                //throw $th;
                notify()->warning($th->getMessage());
                return back();
            }
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // manager store balance_transfer
    public function managerProfile($id)
    {
        if (!auth()->user()->can('Managers View Profile')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
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
            return view('content.manager.manager-profile', compact('data'));
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
	
	public function managerDetails()
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
				return view('content.manager.manager-profile', compact('data'));
			} catch (\Throwable $th) {
				//throw $th;
				notify()->warning($th->getMessage());
				return back();
			}
        }
    }
	
    // ====== manager Ladger list====== 
    public function managers_ladger(Request $request)
    {
        if (!auth()->user()->can('managers-ledger')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data =  ManagerBalanceHistory::with('manager', 'invoice', 'invoice.customer', 'app_manager')
                ->when($request->manager, function ($q) use ($request) {
                    return $q->where('manager_id', $request->manager);
                })
                ->whereIn('history_for', [
                    INVOICE_MANAGER_ADD_PANEL_BALANCE,
                    INVOICE_CUSTOMER_ADD_BALANCE,
                    INVOICE_CUSTOMER_MONTHLY_BILL
                ])
                ->when($request->date_range, function ($q) use ($request) {
                    $date = explode('to', $request->date_range);
                    return $q->whereBetween('created_at', [$date]);
                })
                ->latest()->paginate($request->item ?? 10);
            return view('content.manager.manager-ladger', compact('data'));
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
    // manager change Password
    public function managerChangePassword(Request $request, $id)
    {
        // return redirect()->route('pages-misc-under-maintenance');
        if (!auth()->user()->can('Managers change-password')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
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
    // manager change profile
    /* profile_old
    * profile_for
    * profile 
    */
    public function update_profile(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            if ($request->hasFile('profile')) {
                if ($request->profile_for == 'manager') {
                    ManagerModel::find($id)->update(['profile_photo_url' => fileUpload($request->profile, 'uploads/manager/', $request->profile_old)]);
                } elseif ($request->profile_for == 'customer') {
                    Customer::find($id)->update(['avater' => fileUpload($request->profile, 'uploads/customer/', $request->profile_old)]);
                }
            }
            DB::commit();
            notify()->success('Profile Change Successfully');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }

    // franchise add custom package price
    public function franchise_add_custom_pkg_price(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required'
        ]);
        if ($validator->fails()) return error_message('Price Field is Requard');
        try {
            ManagerAssignPackage::find($id)->update(['manager_custom_price' => $request->price]);
            notify()->success('Custom Price Added Successfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }

    // /manager_update_panel_balance
    public function manager_update_panel_balance(Request $request, $id)
    {
        if (!auth()->user()->can('Managers Add Custom Balance')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        DB::beginTransaction();
        try {
            $manager_balance_history = ManagerBalanceHistory::find($id);
            $manager = ManagerModel::where('id', $manager_balance_history->manager_id)->first();
            if ($request->action_for == STATUS_ACCEPTED) {
                $manager->increment('panel_balance', $manager_balance_history->balance);
                ManagerModel::where('id', $manager_balance_history->app_manager_id)->increment('wallet', $manager_balance_history->balance);
                // DailyExpense::create(['manager_id' => auth()->user()->id]);
            } else {
                Invoice::where('id', $manager_balance_history->invoice_id)->update(['status' => STATUS_REJECTED, 'comment' => "franchise $request->action_for his balance", 'received_amount' => 00]);
            }
            $manager_balance_history->update(['status' => $request->action_for, 'franchise_panel_balance' => $manager->panel_balance, 'note' => "franchise $request->action_for his balance"]);
            notify()->success("$request->action_for Successfully");
            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }
}
