<?php

namespace App\Http\Controllers\mikrotik;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use App\Services\ConnectionService;
use App\Models\Mikrotik as MikrotikModal;
use App\Models\IPpool;
use App\Models\Manager;
use App\Models\Package;
use App\Models\PppUser;
use Illuminate\Support\Facades\Auth;

class Mikrotik extends Controller
{
    public function mikrotik_info(Request $request, $id)
    {
        try {
            $mikrotik  = MikrotikModal::find($id);
            $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
            $q_data = ($request->has('username') && $request->username !== null ? "<pppoe-" . $request->username . ">" : ($request->has('ether_name') && $request->ether_name !== null ? $request->ether_name : '1.SFP-2-NEXSUS-SW-0/0/2'));

            if ($request->has('call_ip')) {
                try {
                    //pool*
                    $ARRAY = $connection->system_info($q_data);
                    if (gettype($ARRAY) == 'string') return false;

                    if (count($ARRAY) > 0) {
                        $rx = $ARRAY[0]["rx-bits-per-second"]; // uploadmbps
                        $tx = $ARRAY[0]["tx-bits-per-second"]; //downloadmbps
                        $rows['name'] = 'Tx';
                        $rows['data'][] = $tx;
                        $rows2['name'] = 'Rx';
                        $rows2['data'][] = $rx;
                        $rows3['name'] = 'mx';
                        $rows3['data'][] = $ARRAY;
                    } else {
                        echo $ARRAY['!trap'][0]['message'];
                    }

                    $result = array();
                    array_push($result, $rows);
                    array_push($result, $rows2);
                    array_push($result, $rows3);

                    return response()->json($result);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            } else {
                $re_data = $connection->printEthernet();
                $ethernates = array();
                foreach ($re_data as $key => $value) {
                    $ethernates[] = $value['name'];
                }
                $mikrotik_name = $mikrotik->identity;
                return view('content.mikrotik.mkt-info', compact('ethernates', 'mikrotik_name'));
            }
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }
    //mikrotik_system_resource
    public function mikrotik_system_resource(Request $request, $id)
    {
        try {
            $mikrotik  = MikrotikModal::find($id);
            $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
            $res_data = $connection->printSystemResource();
            return view('content.mikrotik.mkt-system-print', compact('res_data'));
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }
    public function addMikrotik()
    {
        if (!auth()->user()->can('Mikrotik Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        return view('content.mikrotik.add-mikrotik');
    }
    public function storeMikrotik(Request $request)
    {
        if (!auth()->user()->can('Mikrotik Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'identity' => 'required',
            'username' => 'required|max:255',
            'password' => 'required',
            'host'     => "required|ipv4|unique:mikrotiks,host",
            'port'     => 'required',
        ]);

        try {
            new ConnectionService($request->get('host'), $request->get('username'), $request->get('password'), $request->get('port'));
            $mikrotik = new MikrotikModal();
            $mikrotik->identity = $request->identity;
            $mikrotik->host = $request->host;
            $mikrotik->username = $request->username;
            $mikrotik->password = $request->password;
            $mikrotik->port = $request->port;
            $mikrotik->status = TRUE;
            $mikrotik->address = $request->address;
            $mikrotik->sitename = $request->sitename;
            $mikrotik->user_id = 2;
            $mikrotik->save();
            // return success_message("Data Created Successfully", $mikrotik);
            return redirect()->route('mikrotik-view-mikrotik');
            notify()->success("Mikrotik Added  Successfully");
        } catch (Exception $e) {
            notify()->error($e->getMessage());
            // return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
            return back();
        }
        // if ($this->user->hasPermissionTo('Add Mikrotik', 'api')) {
        //     //create client
        //     new ConnectionService($request->get('host'), $request->get('username'), $request->get('password'), $request->get('port'));
        //     try {
        //         $mikrotik = new Mikrotik();
        //         $mikrotik->identity = $request->identity;
        //         $mikrotik->host = $request->host;
        //         $mikrotik->username = $request->username;
        //         $mikrotik->password = $request->password;
        //         $mikrotik->port = $request->port;
        //         $mikrotik->status = TRUE;
        //         $mikrotik->address = $request->address;
        //         $mikrotik->sitename = $request->sitename;
        //         $mikrotik->user_id = 2;
        //         $mikrotik->save();
        //         return success_message("Data Created Successfully", $mikrotik);
        //     } catch (Exception $e) {
        //         return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
        //     }
        // } else {
        //     return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        // }

    }

    public function viewMikrotik(Request $request)
    {
        if (!auth()->user()->can('Mikrotik')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $manager = Manager::where('id', Auth::user()->id)->first();
        if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
            $mikrotiks = MikrotikModal::paginate($request->item ?? 10);
        } else {
            $mikrotiks = MikrotikModal::where('id', $manager->mikrotik_id)->paginate($request->item ?? 10);
        }
        return view('content.mikrotik.view-mikrotik', compact('mikrotiks'));
    }

    public function editMikrotik($id)
    {
        if (!auth()->user()->can('Mikrotik Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

        $mikrotik = MikrotikModal::where('id', $id)->first();
        return view('content.mikrotik.edit-mikrotik', compact('mikrotik'));
    }

    public function updateMikrotik(Request $request, $id)
    {
        if (!auth()->user()->can('Mikrotik Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'username' => 'required|max:255',
            'password' => 'required',
            'status'   => '',
            'address'  => 'required',
            'port'     => '',
            'sitename' => 'required',
            'host'     => 'required|ipv4|unique:mikrotiks,host,' . $id,
        ]);

        if ($validator->fails()) {
            // return error_message('Validation Error', $validator->errors()->all(), 422);
            return 0;
        } else {
            //create client
            new ConnectionService($request->get('host'), $request->get('username'), $request->get('password'), $request->get('port'));

            $mikrotik = MikrotikModal::find($id);
            $mikrotik->identity = $request->identity;
            $mikrotik->host = $request->host;
            $mikrotik->username = $request->username;
            $mikrotik->password = $request->password;
            $mikrotik->port = $request->port;
            $mikrotik->address = $request->address;
            $mikrotik->sitename = $request->sitename;
            $mikrotik->save();
            notify()->success("Data Update Successfully");
            return redirect()->route('mikrotik-view-mikrotik');
            // try {
            //     if ($this->user->hasPermissionTo('Miktrotik Edit', 'api')) {
            //         $mikrotik = Mikrotik::find($id);
            //         $mikrotik->identity = $request->identity;
            //         $mikrotik->host = $request->host;
            //         $mikrotik->username = $request->username;
            //         $mikrotik->password = $request->password;
            //         $mikrotik->port = $request->port;
            //         $mikrotik->address = $request->address;
            //         $mikrotik->sitename = $request->sitename;
            //         $mikrotik->save();
            //         return success_message("Data Update Successfully", $mikrotik);
            //     } else {
            //         return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            //     }
            // } catch (Exception $e) {
            //     return error_message('Database Exception Error', $e->getMessage(), $e->getCode());
            // }
        }
    }

    public function addToRadius(Request $request, $id)
    {
        if (!auth()->user()->can('Mikrotik Sync')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

        try {
            // if ($this->user->hasPermissionTo('Add Radious', 'api')) {
            $mikrotik = MikrotikModal::where('id', $id)->first();
            if (empty($mikrotik)) return error_message('Mikrotik not found');
            //create client
            $connection = new ConnectionService($mikrotik->host, $mikrotik->username, $mikrotik->password, $mikrotik->port);
            //pool*
            if (isset($request->type) && $request->type == 1 || $request->type == 'sync_all') {
                $query_ppp_pool = $connection->poolPrint();
                $data =  $this->ipPoolPrint($query_ppp_pool, $id);
                // dd($data );
                notify()->success('Pool add success');
                return back();
            }
            //profile*
            if (isset($request->type) && $request->type == 2 || $request->type == 'sync_all') {
                $query_ppp_profile = $connection->profilePrint();
                $this->profilePrint($query_ppp_profile, $id);
                notify()->success('profile add success');
                return back();
            }
            //secret*
            if (isset($request->type) && $request->type == 3 || $request->type == 'sync_all') {
                $query_ppp_secret = $connection->secretprint();
                $this->secretPrint($query_ppp_secret, $id);
                notify()->success('Secret add success');
                return back();
            }
            //activeConnectionUser
            if (isset($request->type) && $request->type == 4 || $request->type == 'sync_all') {
                $query_ppp_active_online_user = $connection->activeConnectionUser();
                return success_message("Get Online User", $query_ppp_active_online_user);
            }
            // disconnectConnectedUser
            if (isset($request->type) && $request->type == 5 || $request->type == 'sync_all') {
                $query_ppp_dactive_online_user = $connection->disconnectConnectedUser();
                return $query_ppp_dactive_online_user;
            }

            //sync queueSimplePrint form the mikrotik 
            if (isset($request->type) && $request->type == 6 || $request->type == 'sync_all') {
                $query_queue_simple = $connection->queueSimplePrint();
                $this->queueSimplePrintCreate($query_queue_simple, $id);
                return success_message("Simple Queue Saved Successfully.");
            }
            //sync queue type form the mikrotik 
            if (isset($request->type) && $request->type == 7 || $request->type == 'sync_all') {
                $query_queue_type = $connection->queueTypePrint();
                $this->queueTypePrintCreate($query_queue_type, $id);
                return success_message("Queue Type Saved Successfully.");
            }
            //sync ethernet form the mikrotik 
            if (isset($request->type) && $request->type == 8 || $request->type == 'sync_all') {
                $query_queue_type = $connection->printEthernet();
                $this->interfaceEthernetCreate($query_queue_type, $id);
                return success_message("Ethernet Saved Successfully.");
            }
            //sync ip Address form the mikrotik 
            if (isset($request->type) && $request->type == 9 || $request->type == 'sync_all') {
                $query_queue_type = $connection->ipAddressPrint();
                $this->ipAddressCreate($query_queue_type, $id);
                return success_message("IP Address Saved Successfully.");
            }

            //sync interface form the mikrotik 
            if (isset($request->type) && $request->type == 11 || $request->type == 'sync_all') {
                $query_data = $connection->printInterface();
                $this->interfaceCreate($query_data, $id);
                return success_message("Interface Saved Successfully.");
            }
            // } else {
            //     return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            // }
        } catch (Exception $exception) {
            info($exception->getMessage());
            // return error_message('Something went wrong!', $exception->getMessage(), $exception->getCode());
            return 0;
        }
    }

    //create ip pool
    public function ipPoolPrint($responses, $mikrotik_id)
    {
        try {
            foreach ($responses as $key => $ip_pool) {
                $range = explode('-', $ip_pool['ranges']);
                $check_ip_pool =  IPpool::where(['name' => $ip_pool['name'], 'mikrotik_id' => $mikrotik_id])->first();
                // dd($check_ip_pool);
                if (!$check_ip_pool) {
                    if (count($range) == 2) {
                        $firstd =  explode('.', current($range));
                        $secondD =  explode('.', end($range));
                        // return [$range, $total_number_of_ip =  end($secondD) - end($firstd) + 1];
                        $total_number_of_ip =  end($secondD) - end($firstd);
                    } else {
                        $range = array_filter(explode('/', $ip_pool['ranges']));
                        $total_number_of_ip =  $this->calculateSubnet(end($range));
                    }
                    $data = [
                        'name'        => $ip_pool['name'] ?? '',
                        'nas_type'    => 'Mikrotik',
                        'type'        => 'PPPOE',
                        'start_ip'    => $range[0] ?? '',
                        'end_ip'      => $range[1] ?? '',
                        'mikrotik_id' => $mikrotik_id,
                        'subnet'      => $total_number_of_ip[1] ?? '',
                        'total_number_of_ip' => $total_number_of_ip[0] ?? $total_number_of_ip,
                    ];
                    IPpool::create($data);
                }
                continue;
            }
        } catch (Exception $e) {
            notify()->warning($e->getMessage());
            return back();
        }
    }

    //create package
    public function profilePrint($responses, $mikrotik_id)
    {
        try {
            foreach ($responses as $profile) {
                $check_is_data_exists =  Package::where('name', $profile['name'])->where('mikrotik_id', $mikrotik_id)->first();
                if (!$check_is_data_exists) {
                    if (isset($profile['remote-address'])) {
                        $check_is_pool_exists =  IPpool::select('name', 'id', 'mikrotik_id')
                            ->when($profile['remote-address'], function ($q) use ($profile) {
                                return $q->where('name', $profile['remote-address']);
                            })->first();
                        if ($check_is_pool_exists) {
                            $pool_id = $check_is_pool_exists->id;
                        } else {
                            $pool_id = 0;
                        }
                    } else {
                        $pool_id = 0;
                    };
                    $data = [
                        'name'            => $profile['name'],
                        'type'            => 'PPPOE',
                        'mikrotik_id'     => $mikrotik_id,
                        'pool_id'         => $pool_id,
                        'local_address'   => isset($profile['local-address']) ? $profile['local-address'] : '',
                        'bandwidth'       => $profile['rate-limit'] ?? '',
                        'status'          => isset($profile['default']) ? $profile['default'] : '',
                    ];
                    Package::create($data);
                }
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    //ppp users
    public function secretPrint($responses, $mikrotik_id, $added_in_customers_table = false)
    {
        try {
            foreach ($responses as $pppuser) {
                $check_pppuser =  PppUser::where(['name' => $pppuser['name'], 'mikrotik_id' => $mikrotik_id])->first();
                if (!$check_pppuser) {
                    $ppp = new PppUser();
                    $ppp->id_in_mkt = $pppuser['.id'];
                    $ppp->added_in_customers_table = $added_in_customers_table;
                    $ppp->mikrotik_id = $mikrotik_id;
                    $ppp->name = isset($pppuser['name']) ? $pppuser['name'] : '';
                    $ppp->service = isset($pppuser['service']) ? $pppuser['service'] : '';
                    $ppp->password = isset($pppuser['password']) ? $pppuser['password'] : '';
                    $ppp->profile = isset($pppuser['profile']) ? $pppuser['profile'] : '';
                    if (!empty($pppuser['local-address'])) {
                        $ppp->localAddress = $pppuser['local-address'];
                    }
                    if (!empty($pppuser['remote-address'])) {
                        $ppp->remoteAddress = $pppuser['remote-address'];
                    }
                    if (!empty($pppuser['only-one'])) {
                        $ppp->onlyOne = $pppuser['only-one'];
                    }
                    if (!empty($pppuser['rate-limit'])) {
                        $ppp->rateLimit = $pppuser['rate-limit'];
                    }
                    if (!empty($pppuser['dns-server'])) {
                        $ppp->dns = $pppuser['dns-server'];
                    }
                    $ppp->status  = isset($pppuser['disabled']) ? $pppuser['disabled'] : 'true';
                    $ppp->save();
                }
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function calculateSubnet($data)
    {
        if ($data == 8) {
            $data =  [16777214, "255.0.0.0"]; // return first toatal number of ip and 2nd subnetmask
        } elseif ($data == 9) {
            $data =  [8388606, "255.128.0.0"];
        } elseif ($data == 10) {
            $data =  [4194302, '255.192.0.0'];
        } elseif ($data == 11) {
            $data =  [2097150, '255.224.0.0'];
        } elseif ($data == 12) {
            $data =  [1048574, '255.240.0.0'];
        } elseif ($data == 13) {
            $data =  [524286, '255.248.0.0'];
        } elseif ($data == 14) {
            $data =  [262142, '255.252.0.0'];
        } elseif ($data == 15) {
            $data =  [131070, '255.254.0.0'];
        } elseif ($data == 16) {
            $data =  [65534, '255.255.0.0'];
        } elseif ($data == 17) {
            $data = [32766, '255.255.128.0'];
        } elseif ($data == 18) {
            $data = [16382, '255.255.192.0'];
        } elseif ($data == 19) {
            $data = [8190, '255.255.224.0'];
        } elseif ($data == 20) {
            $data = [4094, '255.255.240.0'];
        } elseif ($data == 21) {
            $data = [2046, '255.255.248.0'];
        } elseif ($data == 22) {
            $data = [1022, '255.255.252.0'];
        } elseif ($data == 23) {
            $data = [210, '255.255.254.0'];
        } elseif ($data == 24) {
            $data = [254, '255.255.255.0'];
        } elseif ($data == 25) {
            $data = [126, '255.255.255.128'];
        } elseif ($data == 26) {
            $data = [62, '255.255.255.192'];
        } elseif ($data == 27) {
            $data = [30, '255.255.255.224'];
        } elseif ($data == 28) {
            $data = [14, '255.255.255.240'];
        } elseif ($data == 29) {
            $data = [6, '255.255.255.248'];
        } elseif ($data == 30) {
            $data = [2, '255.255.255.252'];
        } elseif ($data == 31) {
            $data = [0, '255.255.255.254'];
        } elseif ($data == 32) {
            $data = [0, '255.255.255.255'];
        } else {
            $data = [0, '000:000:000:000'];
        }
        return $data;
    }
    //showMikrotikIpPool
    public function showMikrotikIpPool(Request $request)
    {
        $ips = IPpool::when($request, function ($q) use ($request) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('mikrotik_id', $request->mikrotik ?? auth()->user()->mikrotik_id);
            } else {
                if ($request->mikrotik) return $q->where('mikrotik_id', $request->mikrotik);
            }
        })->latest()->paginate($request->item ?? 10);
        $mikrotiks = MikrotikModal::select('id', 'identity', 'host')->get();
        return view('content.mikrotik.ip-pool', compact('ips', 'mikrotiks'));
    }
    // saveMikrotikIpPool 
    public function saveMikrotikIpPool(Request $request)
    {
        $request->validate([
            'mikrotik_id' => 'required',
            'name' => 'required',
            'subnet' => 'required',
            'start_ip' => 'required',
            'end_ip' => 'required',
            'total_number_of_ip' => 'required',
            'public_ip' => 'required'
        ]);
        if ($request->chargeable_ip == '1') {
            $request->chargeable_ip = true;
        } else {
            $request->chargeable_ip = false;
        }

        $ip = IPpool::where('name', $request->name)->first();
        if (!$ip) {
            $start_ip = IPpool::where('start_ip', $request->start_ip)->first();
            if (!$start_ip) {
                $end_ip = IPpool::where('end_ip', $request->end_ip)->first();
                if (!$end_ip) {
                    IPpool::create([
                        'name'        => $request->name,
                        'nas_type'    => 'Mikrotik',
                        'type'        => 'PPPOE',
                        'start_ip'    => $request->start_ip,
                        'end_ip'      => $request->end_ip,
                        'mikrotik_id' => $request->mikrotik_id,
                        'subnet'      => $request->subnet,
                        'total_number_of_ip' => $request->total_number_of_ip,
                        'public_ip' => $request->public_ip,
                        'is_ip_charge' => $request->chargeable_ip,
                        'public_ip_charge' => $request->charge
                    ]);

                    return back();
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    // editMikrotikIpPool
    public function editMikrotikIpPool($id)
    {
        $ip = IPpool::where('id', $id)->first();
        $mikrotiks = MikrotikModal::all();
        return view('content.mikrotik.edit-ip-pool', compact('ip', 'mikrotiks'));
    }

    public function updateMikrotikIpPool(Request $request, $id)
    {
        $request->validate([
            'mikrotik_id' => 'required',
            'name' => 'required',
            'start_ip' => 'required',
            'end_ip' => 'required',
            'total_number_of_ip' => 'required',
            'public_ip' => 'required'
        ]);

        if ($request->public_ip == 'yes') {
            if ($request->chargeable_ip == '1') {
                $request->chargeable_ip = true;
            } else {
                $request->chargeable_ip = false;
            }
        }

        $ip = IPpool::find($id);

        $ip_name = IPpool::whereNot('id', $id)->where('name', $ip->name)->first();

        if (!$ip_name) {
            $start_ip = IPpool::whereNot('id', $id)->where('start_ip', $ip->start_ip)->first();
            if (!$start_ip) {
                $end_ip = IPpool::whereNot('id', $id)->where('end_ip', $ip->wnd_ip)->first();
                if (!$end_ip) {
                    $ip->name = $request->name;
                    $ip->subnet = $request->subnet;
                    $ip->start_ip = $request->start_ip;
                    $ip->end_ip = $request->end_ip;
                    $ip->total_number_of_ip = $request->total_number_of_ip;
                    $ip->public_ip = $request->public_ip;
                    if ($request->public_ip == 'yes') {
                        $ip->is_ip_charge = $request->chargeable_ip;
                        $ip->public_ip_charge = $request->charge;
                    } else {
                        $ip->is_ip_charge = false;
                        $ip->public_ip_charge = null;
                    }
                    $ip->save();
                    return redirect()->route('mikrotik-ip-pool');
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * delete data form the resource.
     * @parame $id
     * @return \Illuminate\Http\Response
     */

    public function delete_ip_pool($id)
    {
        try {
            $data = IPpool::find($id);
            if ($data->subnet == null) {
                $data->delete();
                notify()->success('Delete successfully');
                return back();
            } else {
                notify()->warning('You cannot delete this');
            }
            return back();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
