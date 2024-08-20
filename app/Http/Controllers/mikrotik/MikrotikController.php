<?php

namespace App\Http\Controllers\mikrotik;

use Exception;
use App\Models\Mikrotik;
use App\Http\Controllers\Controller;
use App\Services\User\UserConnectionService;

class MikrotikController extends Controller
{
    private $user_connection_service;
    public $query_data = '';
    public $user;

    public function __construct(
        UserConnectionService $user_connection_service,
    ) {
        $this->user_connection_service = $user_connection_service;
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $mikrotik = Mikrotik::find($id);
        return response()->json([
            'mikrotik' => $mikrotik
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Mikrotik::with(
                'customers',
                'managers',
                'ippools',
                'packages',
            )->find($id);
            if ($data->customers->count() > 0 || $data->managers->count() > 0 || $data->ippools->count() > 0 || $data->packages->count() > 0) {
                notify()->warning('Please Delete assigned Data first Like Customers, Managers, IP Pool or packages');
                return back();
            }
            $data->delete();
            notify()->success('Deleted');
            return back();
        } catch (Exception $e) {
            ////=======handle DB exception error==========\
            notify()->success($e->getMessage());
        }
    }
}
