<?php

namespace App\Http\Controllers;

use App\Exports\MikrotikUserExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\mikrotik\Mikrotik as MikrotikMikrotik;
use App\Models\Customer;
use App\Models\CustomerPackageChangeHistory;
use App\Models\Invoice;
use App\Models\Manager;
use App\Models\Mikrotik;
use App\Models\Package;
use App\Models\PppUser;
use App\Services\ConnectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportImportController extends Controller
{
    /**
     * 👉 import_customer
     *  Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportExpireCustomer(Request $request)
    {
        // if (!auth()->user()->can('BTRC Report Export')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $customer = Customer::with('zone', 'manager', 'purchase_package')
            ->when(auth()->user(), function ($q) {
                if (auth()->user()->type == FRANCHISE_MANAGER) {
                    return $q->where('manager_id', auth()->user()->id);
                }
            })->where(['status' => CUSTOMER_EXPIRE, 'mikrotik_disabled' => STATUS_FALSE])
            ->get();
        // dd($customer);
        //name,phone,username,zone,address,package, monthly bill, expire_date -->
        $arraydata = array(
            array(
                'name',
                'phone',
                'username',
                'zone',
                'address',
                'package',
                'monthly bill',
                'expire_date',
            )
        );
        foreach ($customer as $key => $item) {
            $arr = array(
                $item->full_name, // name
                $item->phone, // phone
                $item->username, // username
                $item->zone ? $item->zone->name : '', // zone
                $item->address, // address
                $item->purchase_package ? $item->purchase_package->name : '', // package
                $item->bill - $item->discount, // monthly bill
                $item->expire_date // expire_date
            );
            $arraydata[] = $arr;
        }
        $customData = new Collection($arraydata);
        notify()->success('Csv Download Successfully');
        $name = 'Expire-customer' . Date::now()->format('d-M-Y H:i a') . ".$request->type";
        return Excel::download(new MikrotikUserExport($customData), "$name");
    }
    /**
     * 👉 import_customer
     *  Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function userExport(Request $request)
    {
        // if (!auth()->user()->can('BTRC Report Export')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
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
            })->where('added_in_customers_table', false)
            ->get();
        $arraydata = array(
            array(
                'full_name*',
                'email*',
                'gender',
                'national_id',
                'phone*',
                'date_of_birth*',
                'father_name',
                'mother_name',
                'address*',
                'zone_id (number only)*',
                'sub_zone_id (number only)',
                'registration_date*',
                'connection_date*',
                'expire_date (Example:2023-08-22 21:03:00)*',
                'package_id (number only)*',
                'purchase_package_id (number only)*',
                'mikrotik_id (number only)*',
                'bill (number only)*',
                'discount (number only)',
                'wallet (number only)',
                'service*',
                'username*',
                'password*',
                'manager_id (add hare app user mysql id)*',
            )
        );
        foreach ($mikrotik_users as $key => $item) {
            $package = Package::where('name', $item->profile)->first();
            $package_id = $package ? $package->id : '';
            if ($package) {
                $price = auth()->user()->type ==  FRANCHISE_MANAGER ? $package->franchise_price : $package->price;
            } else {
                $price = '';
            }
            $arr = array(
                $item->name, // 'full_name',
                "", // 'email',
                "", // 'gender',
                "", // 'national_id',
                "", // 'phone',
                "", // 'date_of_birth',
                "", // 'father_name',
                "", // 'mother_name',
                "", // 'address',
                "", // 'zone_id',
                "", // 'sub_zone_id',
                "", // 'registration_date',
                "", // 'connection_date',
                "", // 'expire_date',
                $package_id, // 'package_id',
                $package_id, // 'purchase_package_id',
                $request->mikrotik_id, // 'mikrotik_id',
                $price, // 'bill',
                "", // 'discount',
                "", // 'wallet',
                "PPoE", // 'service',
                $item->name, // 'username',
                $item->password, // 'password'
                "", // 'manager_id'
            );
            $arraydata[] = $arr;
        }
        $customData = new Collection($arraydata);
        notify()->success('Mikrotik Users Csv Download Successfully');
        $name = 'mikrotik-users' . Date::now()->format('d-M-Y H:i a') . '.csv';
        return Excel::download(new MikrotikUserExport($customData), "$name");
    }
    // 👉 import_customer
    function import_customer(Request $request)
    {
        $auth = auth()->user();
        try {
            $existsdata = [];
            if (!$request->file)  return error_message('Please Input .csv File');
            $file = $request->file('file');
            // File Details 
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            // Valid File Extensions
            $valid_extension = array("csv");
            // 2MB in Bytes
            $maxFileSize = 2097152;
            // Check file extension
            if (in_array(strtolower($extension), $valid_extension)) {
                // Check file size
                if ($fileSize <= $maxFileSize) {
                    // File upload location
                    $location = 'uploads';
                    // Upload file
                    $file->move($location, $filename);
                    // Import CSV to Database
                    $filepath = public_path($location . "/" . $filename);
                    // Reading file
                    $file = fopen($filepath, "r");
                    $importData_arr = array();
                    $i = 0;
                    $calculate_bills = 0;
                    $imp_manager_id = null;
                    while (($filedata = fgetcsv($file)) !== FALSE) {
                        $num = count($filedata);
                        // Skip first row (Remove below comment if you want to skip the first row)
                        if ($i == 0) {
                            $i++;
                            continue;
                        }
                        for ($c = 0; $c < $num; $c++) {
                            if ($c == 17) $calculate_bills += $filedata[$c];

                            if ($c == 23) {
                                if ($imp_manager_id == null) $imp_manager_id = $filedata[$c];
                                if ($imp_manager_id !== null && $imp_manager_id !== $filedata[$c]) return error_message($i + 1 . " Number Row has different manager id");
                            }
                            $importData_arr[$i][] = $filedata[$c];
                        }
                        $i++;
                    }
                    // file close 
                    fclose($file);
                    // file close 
                    $imported_manager = Manager::select('id', 'type', 'wallet', 'panel_balance', 'mikrotik_id',)->where('id', $imp_manager_id)->first();
                    if (!$imported_manager) return error_message('Manager Not Found');
                    if ($imported_manager->type == FRANCHISE_MANAGER) {
                        if ($imported_manager->panel_balance < $calculate_bills) return error_message("No enough balance, total required bill maximum $calculate_bills Tk");
                    }
                    // Insert to MySQL database
                    foreach ($importData_arr as $index => $col) {
                        if ($col[14] == '') return error_message("Row $index package_id is required");
                        if ($col[15] == '') return error_message("Row $index purchase_package_id is required");
                        if ($col[16] == '') return error_message("Row $index Mikrotik_id required");
                        if ($col[17] == '') return error_message("Row $index bill is required");
                        if ($col[21] == '') return error_message("Row $index username required");
                        if ($col[22] == '') return error_message("Row $index username required");
                        if ($col[23] == '') return error_message("Row $index manager_id is required");

                        $phone = $col[4];
                        $mikrotik_id = $col[16];
                        $username = $col[21];
                        $zone = $col[9];
                        $connection_date = $col[12];
                        $expire_date = $col[13];
                        $user_password = $col[22];

                        $customer = Customer::with('mikrotik', 'package')->where(['username' => $username, 'mikrotik_id' => $mikrotik_id])->first();

                        if (!$customer) {
                            $id_in_mkt = '';
                            //   find and check if exists ppp user
                            $ppp_user =    PppUser::select('name', 'id', 'id_in_mkt', 'added_in_customers_table')->where('name', $username)->first();
                            if ($ppp_user) {
                                $ppp_user->added_in_customers_table = true;
                                $ppp_user->save();
                                $id_in_mkt = $ppp_user->id_in_mkt;
                            }
                            $data = new  Customer();
                            $data->id_in_mkt         = $id_in_mkt;
                            $data->full_name         = $col[0];
                            $data->email             = $col[1];
                            $data->gender            = ucfirst($col[2]);
                            $data->national_id       = $col[2];
                            $data->phone             = $col[4];
                            $data->date_of_birth     = $col[5];
                            $data->father_name       = $col[6];
                            $data->mother_name       = $col[7];
                            $data->address           = $col[8];
                            $data->zone_id           = $col[9] !== '' ? $col[9] : null;
                            $data->sub_zone_id       = $col[10] !== '' ? $col[10] : null;
                            $data->registration_date = $col[11];
                            $data->connection_date   = $col[12];
                            $data->expire_date       = $col[13] !== '' ? $col[13] : null;
                            $data->package_id        = $col[14] !== '' ? $col[14] : null;
                            $data->purchase_package_id = $col[15] !== '' ? $col[15] : null;
                            $data->mikrotik_id       = $col[16] !== '' ? $col[16]  : null;
                            $data->bill              = $col[17] !== '' ? $col[17]  : null;
                            $data->discount          = $col[18] !== '' ? $col[18]  : null;
                            $data->wallet            = $col[19] !== '' ? $col[19]  : 00;
                            $data->service           = $col[20] !== '' ? $col[20]  : null;
                            $data->username          = $col[21] !== '' ? $col[21]  : null;
                            $data->password          = $col[22] !== '' ? $col[22]  : null;
                            $data->status            = CUSTOMER_ACTIVE;
                            $data->manager_id        = $col[23];
                            $data->customer_for      = $imported_manager->type;
                            //  check mikrotik
                            if ($imported_manager->type == FRANCHISE_MANAGER) {
                                if ($imported_manager->mikrotik_id !== $mikrotik_id) return error_message("Row  $index Wrong Mikrotik id given");
                            }
                            $mikrotik = Mikrotik::select('id', 'host', 'username',    'password', 'port')->where('id', $mikrotik_id)->first();
                            if (!$mikrotik) return error_message("Row $index Mikrotik Not Found");

                            //mikrotik user check for test 
                            // $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
                            // $query_ppp_secret = $connection->secretprint();
                            // dd($query_ppp_secret);
                            //mikrotik user check for test 


                            // cheCk package
                            $user_package = Package::select('id', 'name', 'price')->where('id', $data->package_id)->first();
                            // dd(str_replace(',', '', number_format($user_package->price)), $data->bill);
                            if (!$user_package) return error_message('purchase package Not Found');
                            $package_price =  $data->customer_for == APP_MANAGER ?  $user_package->price : $user_package->franchise_price;
                            if (str_replace(',', '', number_format($user_package->price)) !== $data->bill) return error_message("Row  $index bill is not same purchase price");

                            $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
                            $query_data = $connection->importUserInMikrotik([
                                'username' => $username,
                                'password' => $user_password,
                                'package_name'  => $user_package->name,
                                'phone'         => $phone,
                                'zone'          => $zone,
                                'connection_date' => $connection_date,
                                'expire_date'   => $expire_date,
                            ]);
                            if ($query_data !== null) {
                                $mikrotik_controller = new MikrotikMikrotik;
                                $data->id_in_mkt  = $query_data[0]['.id'];
                                $data->save();
                                $mikrotik_controller->secretPrint($query_data, $mikrotik_id, true);
                                // update data insert mikrotik id

                                // 👉create and paid invoice    
                                $invoice = Invoice::create([
                                    'customer_id'       => $data->id,
                                    'invoice_no'        => "INV-{$data->id}-" . date('m-d-Hms-Y'),
                                    'invoice_for'       => INVOICE_NEW_USER,
                                    'package_id'        => $data->package_id,
                                    'zone_id'           => $data->zone_id,
                                    'sub_zone_id'       => $data->sub_zone_id ?? null,
                                    'amount'            => $data->bill,
                                    'received_amount'   => $data->bill,
                                    'paid_by'           => 'Cash',
                                    'status'        => STATUS_PAID,
                                    'invoice_type'  => INVOICE_TYPE_INCOME,
                                    'manager_for'   => $auth->type,
                                    'comment'       => 'New User From csv import',
                                    'manager_id'    => $auth->id,
                                ]);
                                $imported_manager->increment('wallet',);
                                CustomerPackageChangeHistory::create(['customer_id' => $data->id, 'package_id' => $data->package_id, 'manager_id' => auth()->user()->id, 'expire_date' => $data->expire_date]);
                            } else {
                                $existsdata[] = $username;
                            }
                        } else {
                            $mikrotik = $customer->mikrotik;
                            $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
                            $query_data = $connection->importUserInMikrotik([
                                'username' => $username,
                                'password' => $user_password,
                                'package_name' => $customer->package->name,
                                'phone' => $phone,
                                'zone' => $zone,
                                'connection_date' => $connection_date,
                                'expire_date' => $expire_date,
                            ]);
                            if ($query_data !== null) {
                                $mikrotik_controller = new MikrotikMikrotik;
                                $customer->id_in_mkt  = $query_data[0]['.id'];
                                $customer->save();
                                $mikrotik_controller->secretPrint($query_data, $mikrotik_id, true);
                            } else {
                                $existsdata[] = $username;
                            }
                        }
                    }
                } else {
                    return error_message('bigger file size');
                }
            }
            notify()->success('User Import Successfully');
            if (count($existsdata) > 0) {
                return view('content.user.import-customer-reurn-after-op-success', compact('existsdata'));
            } else {
                return back();
            }
        } catch (\Throwable $th) {
            dd($th);
            DB::rollBack();
            ////=======handle DB exception error==========
            return error_message('Database Exception Error', $th->getMessage(), $th->getCode());
        }
    }
}
