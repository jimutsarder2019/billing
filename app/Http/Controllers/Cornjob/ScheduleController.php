<?php

namespace App\Http\Controllers\Cornjob;

use App\Http\Controllers\Controller;
use App\Http\Controllers\customer\Customer;

class ScheduleController extends Controller
{
    public $customerClass;
    public function __construct(Customer $customer)
    {
        $this->customerClass = $customer;
        // $customer->disconnectExpiredCustomer();
        $this->index();
    }

    function index()
    {
        try {
            $this->customerClass->disconnectExpiredCustomer();
            notify()->success('System Run Successfully');
            return back();
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
