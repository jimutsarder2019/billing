<?php

namespace App\Services;

use App\Models\IPpool;
use App\Models\Package;
use App\Models\UserConnectionInfo;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Auth;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;


class ConnectionService
{
    private $client;
    public $expire_date = '';
    public function __construct($host, $user, $pass, $port)
    {
        $config =  (new Config())
            ->set('timeout', 5)
            ->set('host', $host)
            ->set('user', $user)
            ->set('pass', $pass)
            ->set('port', (int) $port);
        $config =  $this->client = new Client($config);
    }

    /**
     * add a user to mikrotik
     *
     * @param [type] $request
     * @return void
     */
    public function addUserToMikrotik($customer, $r = null)
    {
        try {
            $pack = Package::find($customer['package_id']);
            $connection_date = date('d/m/Y H:i a', strtotime($customer->connection_date));
            if ($r['custom_expire_date']) {
                $expire_date = Carbon::parse($r['custom_expire_date']);
            } elseif ($r['custom_payment_duration']) {
                $expire_date = Carbon::now();
            } else {
                // dd($request->connection_date);
                if ($pack->durationmeasure !== null) {
                    $period = explode(' ', $pack->durationmeasure)[0];
                    $duration = explode(' ', $pack->durationmeasure)[1];
                    switch ($duration) {
                        case 'Minutes':
                            $expire_date = Carbon::now()->addMinutes($period);
                            break;
                        case 'Hours':
                            $expire_date = Carbon::now()->addHours($period);
                            break;
                        case 'Days':
                            $expire_date = Carbon::now()->addDays($period);
                            break;
                        case 'Weeks':
                            $expire_date = Carbon::now()->addWeek($period);
                            break;
                        case 'Months':
                            $expire_date = Carbon::now()->addMonth($period);
                            break;
                        default:
                            $expire_date = Carbon::now()->addMonth(1);
                    }
                } else {
                    $expire_date = Carbon::now()->addMonth(1);
                }
            }
            // check custom_payment_duration for advance month and decrese 1 monthe if already added  
            if ($r->custom_payment_duration) $expire_date->addMonth($r->custom_payment_duration);

            //formet expire date for mikrotik comment 
            $expire_coment_date = $expire_date->format('d/m/Y H:i a');
            // mikrotik comment
            $comment = "Phone:" . ($customer->phone !== '' ? $customer->phone : 'N/A') . "| Zone:" . ($customer->zone_id !== null ? $customer->zone->name : 'N/A') . " | Package: $pack->name  | Connection Date: " . ($customer->connection_date !== '' ? $connection_date : 'N/A') . " | Exprire Date: $expire_coment_date";
            //  serve query  in mikrotik
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $customer['username'])
                ->equal('password', $customer['password'])
                ->equal('service', 'pppoe')
                ->equal('profile', $pack['name'])
                ->equal('comment', $comment)
                ->equal('disabled', 'no');
            $this->client->query($query)->read();
            return  $expire_date;
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * add a user to mikrotik
     *
     * @param [type] $request
     * @return void
     */
    public function importUserInMikrotik(...$args)
    {
        $args = $args[0];

        $username = $args['username'];
        // dd($username);
        $password = $args['password'];
        $package_name = $args['package_name'];
        $phone = isset($args['phone']) ? $args['phone'] : 'N/A';
        $zone = isset($args['zone']) ? $args['zone'] : 'N/A';
        $connection_date = isset($args['connection_date']) ? $args['connection_date'] : 'N/A';
        $expire_date = isset($args['expire_date']) ? $args['expire_date'] : 'N/A';
        try {
            $check_query = (new Query("/ppp/secret/print"))->where('name', $username);
            $query_data = $this->client->query($check_query)->read();

            // remove
            if (!$query_data) {
                $comment = "Phone: $phone | Zone: $zone | Package: $package_name  | Connection Date: $connection_date | Exprire Date: $expire_date";
                //  serve query  in mikrotik
                $query = (new Query('/ppp/secret/add'))
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('service', 'pppoe')
                    ->equal('profile', $package_name)
                    ->equal('comment', $comment)
                    ->equal('disabled', 'no');
                $this->client->query($query)->read();

                $inserted_data = (new Query("/ppp/secret/print"))->where('name', $username);
                $res_inserted_data = $this->client->query($inserted_data)->read();
                return  $res_inserted_data;
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            dd($th);
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }


    /**
     * check_milrotiok_user_status
     *
     * @param [type] $request
     * @return void
     */
    public function delete_ppp_user($mkt_id)
    {
        $check_query = (new Query("/ppp/secret/remove"))->equal('numbers', $mkt_id);
        $rmUser = (new Query('/interface/pppoe-server/remove'))->equal('numbers', $mkt_id);
        $query_data = $this->client->query($check_query)->read();
        $query_data = $this->client->query($rmUser)->read();
    }
    /**
     * check_milrotiok_user_status
     *
     * @param [type] $request
     * @return void
     */
    public function system_info($interface = '1.SFP-2-NEXSUS-SW-0/0/2')
    {
        $query = (new Query('/interface/monitor-traffic'))
            ->equal('interface', $interface)
            ->equal('once');
        return $query_data = $this->client->query($query)->read();
    }
    /**
     * check_milrotiok_user_status
     *
     * @param [type] $request
     * @return void
     */
    public function check_milrotiok_user_status($username)
    {
        $query = (new Query("/ppp/active/print"))->where('name', $username);
        // $query = (new Query('/interface/monitor-traffic'))
        //     ->equal('interface', "<pppoe-$username>")
        //     ->equal('once');
        // return $query_data = $this->client->query($query)->read();

        // $query_ppp_deactive_user = (new Query("/interface/pppoe-server/print"))->where('name', "<pppoe-$username>")->where('detail', true);
        // $interface_user = $this->client->query($query_ppp_deactive_user)->read();
        // dd($interface_user);
        $query_data = $this->client->query($query)->read();
        if ($query_data) {
            // add last last-logged-out
            $query = (new Query("/ppp/secret/print"))->where('name', $username);
            $query_secret_data =  $this->client->query($query)->read();
            $query_data[0]['last-logged-out'] = $query_secret_data[0]['last-logged-out'];
            // add last last-logged-out end
            return [
                'query' => $query_data,
                'status' => 'online',
            ];
        } else {
            $query = (new Query("/ppp/secret/print"))->where('name', $username);
            $query_data =  $this->client->query($query)->read();
            return [
                'query' => $query_data,
                'status' => 'ofline',
            ];
        }
    }

    /**
     * add a user to mikrotik
     *
     * @param [type] $request
     * @return void
     */
    public function updateUserToMikrotik($request, $oldData, $expire_date)
    {
        $query_ppp_pool = (new Query("/ppp/secret/print"))->where('name', $oldData->username);
        $secrets  =  $this->client->query($query_ppp_pool)->read();
        $profile = Package::where('id', $request->package)->first();
        if (!$profile) return false;
        $exprireDate = $expire_date !== null ? $expire_date : $profile->fixed_expire_time;
        $comment = "Phone: $request->phone  | Zone:" . ($request->zone !== '' ? $request->zone : 'N/A') . " | Package: $profile->name  | Connection Date: " . ($request->connection_date !== '' ? $request->connection_date : 'N/A') . " | Exprire Date: $exprireDate";
        foreach ($secrets as $secret) {
            $query = (new Query('/ppp/secret/set'))
                ->equal('.id', $secret['.id'])
                ->equal('profile', $profile->name)
                ->equal('password', $request->userpassword)
                ->equal('service', $request->service ? $request->service : 'pppoe')
                ->equal('comment', $request->router_component !== null ? $request->router_component : $comment);
            $this->client->query($query)->read();
        }
        $query_ppp_pool = (new Query("/ppp/secret/print"))->where('name', $oldData->username);
        return $this->client->query($query_ppp_pool)->read();
    }
    /**
     * Change a user to Profile
     *
     * @param [type] $request
     * @return void
     */
    public function addQueueTargetAddress($req)
    {
        $query = (new Query('/queue/simple/print'))->where('name', $req->queue_name);
        $query_profile =  $this->client->query($query)->read();
        foreach ($query_profile as $p) {
            $trdata =  str_replace(array("$req->target_address/32"), "", $p['target']);
            // return   $trdata;
            $query = (new Query('/queue/simple/set'))
                ->equal('.id', $p['.id'])
                ->equal('target', $trdata . ',' . $req->target_address);
            $this->client->query($query)->read();
        }
        $query = (new Query('/queue/simple/print'))->where('name', $req->queue_name);
        return  $this->client->query($query)->read();
    }
    /**
     * disconnect Queue change target address a user to Profile
     *
     * @param [type] $request
     * @return void
     */
    public function removeQueueTargetAddress()
    {
        $query = (new Query('/queue/simple/print'))->where('name', 'test');
        $query_profile =  $this->client->query($query)->read();
        foreach ($query_profile as $p) {
            // $trdata =  str_replace(array('192.168.168.168/32'), "", $p['target']);
            $query = (new Query('/queue/simple/set'))
                ->equal('.id', $p['.id'])
                ->equal('target', $p['target'] . ',' . '10.10.99.99');
            $this->client->query($query)->read();
        }
        $query = (new Query('/queue/simple/print'))->where('name', 'Disconnect');
        return  $this->client->query($query)->read();
    }
    /**
     * Change a user to Profile
     *
     * itemid: "51"
     * mikrotik: "2"
     * user_name: "testuser3"
     * @param [type] $request
     * @return void
     */
    public function changePassword($customer, $request)
    {
        $query_ppp_pool = (new Query("/ppp/secret/print"))->where('name', $customer->username);
        $secrets  =  $this->client->query($query_ppp_pool)->read();
        if ($secrets) {
            $query = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('password', $request->password);
            $this->client->query($query)->read();
            return $this->client->query($query_ppp_pool)->read();
        }
    }
    /**
     * Change a user to Profile
     *
     * itemid: "51"
     * mikrotik: "2"
     * user_name: "testuser3"
     * @param [type] $request
     * @return void
     */
    public function chnageUserProfile($itemid, $user_name, $package)
    {
        $query_ppp_pool = (new Query("/ppp/secret/print"))->where('name', $user_name);
        $secrets  =  $this->client->query($query_ppp_pool)->read();
        if ($secrets) {
            $query = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('profile', $package);
            $this->client->query($query)->read();
            $user_info =  UserConnectionInfo::where('user_id', $itemid)->first();
            if (isset($secrets[0]['remote-address'])) {
                if ($user_info) {
                    $query_data = $this->mikrotik_user_change_status($user_info->username, $user_info->status);
                    if ($query_data) {
                        // return $user_info;
                        $user_info->status = $query_data[0]['disabled'] == 'true' ? 0 : 1;
                        $user_info->save();
                    }
                } else {
                    $user_info->status =  $user_info['status'] == 0 ? 1 : 0;
                    $user_info->save();
                }
                $this->disconnectConnectedUser($user_name);
            }
        }
    }
    /**
     * Change a user to Profile
     *
     * itemid: "51"
     * mikrotik: "2"
     * user_name: "testuser3"
     * @param [type] $request
     * @return void
     */
    public function disconnectUserProfile($itemid, $user_name, $package_name, $d_user_c = true)
    {
        $query_ppp_pool = (new Query("/ppp/secret/print"))->where('name', $user_name);
        $secrets  =  $this->client->query($query_ppp_pool)->read();

        if ($secrets) {
            // dd($secrets);
            if ($secrets && isset($secrets[0]['remote-address'])) {
                $query = (new Query('/ppp/secret/set'))
                    ->equal('.id', $secrets[0]['.id'])
                    ->equal('profile', $package_name)
                    ->equal('disabled', 'true');
                $this->client->query($query)->read();
                $this->disconnectConnectedUser($user_name);
            } else {
                $query = (new Query('/ppp/secret/set'))
                    ->equal('.id', $secrets[0]['.id'])
                    ->equal('profile', $package_name);
                $this->client->query($query)->read();
                $this->disconnectConnectedUser($user_name);
            }
        }
    }

    // activeDisconnectedUser
    public function activeDisconnectedUser($customer, $old_expire_date = null, $customer_old_status = null)
    {
        // 👉
        $query_data = (new Query("/ppp/secret/print"))->where('name', $customer->username);
        $secrets    =  $this->client->query($query_data)->read();
        $zone = $customer->zone ? $customer->zone->name : 'N/A';
        $package = $customer->purchase_package->name;
        $comment = "Phone: $customer->phone | Zone: $zone | Package: $package | Registration Date: $customer->registration_date | Connection Date: $customer->connection_date | Expire Date: $customer->expire_date";
        if ($secrets) {
            if ($customer_old_status == CUSTOMER_ACTIVE) {
                $query = (new Query('/ppp/secret/set'))
                    ->equal('.id', $secrets[0]['.id'])
                    ->equal('comment', $comment);
                $this->client->query($query)->read();
                $updatedData  =  $this->client->query($query_data)->read();
                return $updatedData;
            } else {
                $query = (new Query('/ppp/secret/set'))
                    ->equal('.id', $secrets[0]['.id'])
                    ->equal('disabled', 'false')
                    ->equal('profile', $package)
                    ->equal('comment', $comment);
                $this->client->query($query)->read();
                $updatedData  =  $this->client->query($query_data)->read();
                if (Carbon::parse($old_expire_date) <=  Carbon::now()) {
                    $this->disconnectConnectedUser($customer->username);
                }
                return $updatedData;
            }
        }
    }

    /**
     * 👉 IP Profile print for ppp
     *
     * @return void
     */
    public function profile_add($req)
    {
        //################## check to add new package in mikrotik  start##################
        $query = (new Query('/ppp/profile/add'))
            ->equal('name', $req->name)
            ->equal('local-address', $req->local_address)
            ->equal('remote-address', $req->ip_pool['label']) //like as ip pool
            ->equal('only-one', 'yes')
            ->equal('rate-limit', $req->bandwidth ?? "")
            ->equal('comment', $req->comment ? $req->comment : Carbon::now() . '|' . Auth::user()->name);
        $this->client->query($query)->read();
        //################## check to add new package in mikrotik  start end ##################
    }
    /**
     *👉 IP pool print for ppp
     *
     * @return void
     */
    public function profile_update($req, $prev_name)
    {
        $query_ppp_profile = (new Query('/ppp/profile/print'))->where('name', $prev_name);
        $query_profile =  $this->client->query($query_ppp_profile)->read();
        $ippool =  IPpool::where('id', $req->ip_pool)->first();
        foreach ($query_profile as $p) {
            $query = (new Query('/ppp/profile/set'))
                ->equal('.id', $p['.id'])
                ->equal('name', $req->name)
                ->equal('local-address', $req->local_address)
                ->equal('remote-address', $ippool ? $ippool->name : $p['remote-address']) //like as ip pool
                ->equal('rate-limit', $req->bandwidth)
                ->equal('comment', $req->comment ? $req->comment : Carbon::now() . '|' . Auth::user()->name);
            $this->client->query($query)->read();
        }
        $query_ppp_profile = (new Query('/ppp/profile/print'))->where('name', $req->name);
        return  $this->client->query($query_ppp_profile)->read();
    }
    /**
     * 👉change customer Package
     *
     * @return void
     * 
     */


    public function changeCustomerPackage($customer, $request)
    {
        // dd($request->all()); 
        $req_package = explode('|', $request->package);
        // dd($req_package);
        $query = (new Query('/ppp/secret/print'))->where('name', $customer->username)->where('service', 'pppoe');
        $secrets = $this->client->query($query)->read();
        $string = isset($secrets[0]['comment']) ? $secrets[0]['comment'] : '';

        // Regular expression pattern to match the dynamic content between "Package:" and "|"

        // Replace the dynamic content with "Select Package"
        $updatedString = preg_replace('/Package:\s*(.*?)\s*\|/', "Package:  $req_package[1] |", $string);

        $n_exp_date = isset($request->old_expire_date) ? $request->old_expire_date : (isset($request->custom_expire_date) ? $request->custom_expire_date : null);
        if ($n_exp_date !== null)  $updatedString = preg_replace('/Expire Date:\s*(.*?)$/', "Expire Date: $n_exp_date", $updatedString);

        if ($secrets) {
            $secretQuery = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('profile', $req_package[1])
                ->equal('comment', $updatedString);
            $this->client->query($secretQuery)->read();
            $this->disconnectConnectedUser($customer->username);
            return $this->client->query($query)->read();
        } else {
            return false;
        }
    }
    /**
     *👉 Ethernet update Status
     *
     * @return void
     */
    public function profileChangeStatus($request)
    {
        $result = $request->status == 'true' ? 'false' : 'true';
        $query = (new Query('/ppp/profile/print'))->where('name', $request->name);
        $secrets = $this->client->query($query)->read();
        if ($secrets) {
            foreach ($secrets as $secret) {
                $secretQuery = (new Query('/ppp/profile/set'))
                    ->equal('.id', $secret['.id'])
                    ->equal('default', $result);
                $this->client->query($secretQuery)->read();
            }
            $query = (new Query("/ppp/profile/print"))->where('name', $request->name);
            return $this->client->query($query)->read();
        } else {
            return false;
        }
    }
    /**
     * 👉 IP pool print for ppp
     *
     * @return void
     */
    public function poolPrint()
    {

        //print pool data 
        $query_ppp_pool = new Query('/ip/pool/print');
        return $this->client->query($query_ppp_pool)->read();
    }

    /*
    *👉 req for mac bind specific user name
    *
    * @return void $request
    *
    */
    public function mikrotik_user_change_status($name, $status)
    {
        $query = (new Query('/ppp/secret/print'))->where('name', $name);
        $querysData =  $this->client->query($query)->read();
        $loopquery = (new Query('/ppp/secret/set'))
            ->equal('.id', $querysData[0]['.id'])
            ->equal('disabled', $status == 0 ? 'false' : 'true');
        $this->client->query($loopquery)->read();
        return $this->client->query($query)->read();
    }


    /* 
        *req for mac bind specific user name
        *
       *@return void $request
        *
        */
    public function update_mikrotik_enabled_user($request)
    {
        $query_ppp_pool = (new Query("/ppp/secret/print"))->where('name', 'nayeem');
        $secrets  =  $this->client->query($query_ppp_pool)->read();

        foreach ($secrets as $secret) {
            $query = (new Query('/ppp/secret/set'))
                ->equal('.id', $secret['.id'])
                ->equal('name', 'nayeem')
                ->equal('password', '98765321')
                ->equal('service', 'pppoe')
                ->equal('profile', '15Mbps-1000tk')
                ->equal('disabled', 'false')
                ->equal('comment', 'nayeem comment edited');
            $this->client->query($query)->read();
        }
        $query_ppp_pool = (new Query("/ppp/secret/print"))->where('name', 'nayeem');
        return $this->client->query($query_ppp_pool)->read();
    }
    /* 
     * 👉 req for mac bind specific user name
     *
     *@return void $username
     *
     */
    public function mac_bind($username)
    {
        $query = (new Query("/ppp/secret/print"))->where('name', $username);
        $secrets = $this->client->query($query)->read();
        if ($secrets) {
            // Parse secrets and set password
            foreach ($secrets as $secret) {
                if (isset($secret['last-caller-id']) && $secret['last-caller-id'] !== '') {
                    $secretQuery = (new Query('/ppp/secret/set'))
                        ->equal('.id', $secret['.id'])
                        ->equal('caller-id', $secret['last-caller-id']);
                    $this->client->query($secretQuery)->read();
                } else {
                    $activequery = (new Query("/ppp/active/print"))->where('name', $username);
                    $activrsecrets  =  $this->client->query($activequery)->read();
                    foreach ($activrsecrets as $activrsecret) {
                        $query = (new Query('/ppp/secret/set'))
                            ->equal('.id', $secret['.id'])
                            ->equal('caller-id', $activrsecret['caller-id']);
                        $this->client->query($query)->read();
                    }
                }
            }
            $query = (new Query("/ppp/secret/print"))->where('name', $username);
            return $this->client->query($query)->read();
        } else {
            return false;
        }
    }

    /*
        * 👉 req for mac unbind specific user name
        *
       *@return void $username
        *
        */
    public function mac_unbind($username)
    {
        $query = (new Query("/ppp/secret/print"))->where('name', $username);
        $secrets = $this->client->query($query)->read();
        if ($secrets) {
            // Parse secrets and set password
            foreach ($secrets as $secret) {
                $secretQuery = (new Query('/ppp/secret/set'))
                    ->equal('.id', $secret['.id'])
                    ->equal('caller-id', '');
                // Update query ordinary have no return
                $this->client->query($secretQuery)->read();
            }
            $query = (new Query("/ppp/secret/print"))->where('name', $username);
            return $this->client->query($query)->read();
        } else {
            return false;
        }
    }




    /**
     * 👉 Print Interfaces 
     *
     * @return void
     */
    public function printInterface()
    {
        $query = new Query("/interface/print");
        return $this->client->query($query)->read();
    }


    /**
     * profile print for ppp
     *
     * @return void
     */
    public function profilePrint()
    {
        $query_ppp_profile = new Query('/ppp/profile/print');
        return $this->client->query($query_ppp_profile)->read();
    }

    /**
     * printSystemResource
     *
     * @return void
     */
    public function printSystemResource()
    {
        $query = (new Query("/system/resource/print"));
        return $query_data = $this->client->query($query)->read();
    }
    /**
     * Ethernet print for ppp
     *
     * @return void
     */
    public function printEthernet()
    {
        $query_ppp_secret = new Query('/interface/ethernet/print');
        return $this->client->query($query_ppp_secret)->read();
    }
    /**
     *👉 Ip Address print for ppp
     *
     * @return void
     */
    public function ipAddressPrint()
    {
        $query_ppp_secret = new Query('/ip/address/print');
        return $this->client->query($query_ppp_secret)->read();
    }


    /**
     * 👉 secret print for ppp
     *
     * @return void
     */
    public function secretprint()
    {
        // $query_ppp_secret = new Query('/queue/type/print');
        // return $this->client->query($query_ppp_secret)->read();


        $query_ppp_secret = new Query('/ppp/secret/print');
        return $this->client->query($query_ppp_secret)->read();
    }
    /**
     * 👉 secret print for ppp
     *
     * @return void
     */
    public function activeConnectionUser()
    {
        $query_ppp_active_user = new Query('/ppp/active/print', array("count-only" => "",));
        return $this->client->query($query_ppp_active_user)->read();
    }
    /**
     * 👉 secret print for ppp
     *
     * @return void
     */
    public function allUserByMiktoTik()
    {
        $query_ppp_deactive_user = (new Query("/ppp/active/print"));
        return $this->client->query($query_ppp_deactive_user)->read();
        // totaluser e ppc secreat print 
    }
    /**
     * secret print for ppp
     *
     * @return void
     */
    public function getallmikrotikusers()
    {
        $query_ppp_deactive_user = (new Query("/ppp/secret/print"));
        return $this->client->query($query_ppp_deactive_user)->read();
    }
    /**
     * secret print for ppp
     *
     * @return void
     */
    public function getallmikrotikOnlineusers()
    {
        $query_ppp_deactive_user = (new Query("/ppp/active/print"));
        return $this->client->query($query_ppp_deactive_user)->read();
    }


    /**
     * get desabled status
     * in secret print for ppp
     *
     * @return void
     */
    public function getTotalOfflineMikroTikUsers()
    {
        $query_ppp_deactive_user = (new Query("/ppp/secret/print"))->where('disabled', 'true');
        return $this->client->query($query_ppp_deactive_user)->read();
    }
    /**
     * get desabled status
     * in secret print for ppp
     *
     * @return void
     */
    public function count_mikrotik_offlen_user()
    {
        // $disabled = (new Query('/ppp/secret/print'))->where('disabled', 'true');
        // $disabled =  $this->client->query($disabled)->read();

        $false_active = (new Query('/ppp/secret/print'))->where('disabled', 'false');
        return $false_active =  $this->client->query($false_active)->read();

        // $active = new Query('/ppp/active/print');
        // $active = $this->client->query($active)->read();
        // return $disabled - $active;
    }

    /**
     *Disconnect User form ppp active users
     *
     * @return void
     */

    public function disconnectConnectedUser($username)
    {

        // $query_ppp_deactive_user = new Query("/ppp/secret/print/count-only/where/disabled");
        // $query_ppp_deactive_user = (new Query("/ppp/secret/print"))->where('disabled', 'true');

        // $query_ppp_deactive_user = (new Query("/interface/monitor-traffic"))->equal('interface', 'pppoe-onm57')->equal('once', '');
        // return $this->client->query($query_ppp_deactive_user)->read();
        // $query_ppp_deactive_user = (new Query("  <pppoe-onm57>"));

        $query_ppp_deactive_user = (new Query("/interface/pppoe-server/print"))->where('name', "<pppoe-$username>");
        $user = $this->client->query($query_ppp_deactive_user)->read();
        if ($user) {
            $rmUser = (new Query('/interface/pppoe-server/remove'))->equal('numbers', $user[0]['.id']);
            $this->client->query($rmUser)->read();
        }
    }
}
