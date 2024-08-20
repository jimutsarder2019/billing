<?php

namespace App\Http\Controllers;

use App\Exports\BTRCDataExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DailyExpense;
use App\Models\Invoice;
use App\Models\OLT;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function btrcReport(Request $request)
    {
        if (!auth()->user()->can('BTRC Report')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $req_date_range = $request->date_range;
        try {
            $items = ($request->item && $request->item == 'all') ? Customer::select('id')->count() : ($request->item && $request->item !== 'all' ? $request->item : 10);
            $inv_customer_id = Invoice::when($request->date_range, function ($q) use ($request) {
                $date = explode('to', $request->date_range);
                return $q->whereBetween('updated_at', [$date[0], $date[1]]);
            })->when($request->date_range == null, function ($q) {
                return $q->whereMonth('updated_at',  Carbon::now()->subMonth());
            })
                // ->where(['status' => [STATUS_PAID, STATUS_OVER_PAID], 'invoice_for' => [INVOICE_NEW_USER, INVOICE_CUSTOMER_MONTHLY_BILL]])
                ->where(['status' => STATUS_PAID])
                // ->get();
                ->get()->pluck('customer_id');
            // dd($inv_customer_id);
            // dd($items);
            $data = Customer::whereIn('id', $inv_customer_id)->latest()->with(
                'zone',
                'zone.upazila',
                'zone.upazila.district',
                'zone.upazila.district.division',
                'sub_zone',
                'connection_info',
                'package'
            )->paginate($items);
            return view("content.report.btrc.index", compact('data', 'req_date_range'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $olts = OLT::get();
        $users = Customer::get();
        return view('content.network.add-onu', compact('olts', 'users'));
    }


    // btrcExport
    public function btrcExport(Request $request)
    {
        if (!auth()->user()->can('BTRC Report Export')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $inv_customer_id = Invoice::when($request->date_range, function ($q) use ($request) {
            $date = explode('to', $request->date_range);
            return $q->whereBetween('updated_at', [$date[0], $date[1]]);
        })->when($request->date_range == null, function ($q) {
            return $q->whereMonth('updated_at',  Carbon::now()->subMonth());
        })
            ->where(['status' => STATUS_PAID])
            // ->where(['status' => [STATUS_PAID, STATUS_OVER_PAID], 'invoice_for' => [INVOICE_NEW_USER, INVOICE_CUSTOMER_MONTHLY_BILL]])
            // ->get();
            ->get()->pluck('customer_id');
        $getdata = Customer::whereIn('id', $inv_customer_id)->orderBy('connection_date', 'ASC')->with(
            'zone',
            'zone.upazila',
            'zone.upazila.district',
            'zone.upazila.district.division',
            'sub_zone',
            'connection_info',
            'package'
        )->get();
        $arraydata = array(
            array('client_type', 'connection_type', 'client_name', 'bandwidth_distribution_point', 'connectivity_type', 'activation_date', 'bandwidth_allocation', 'allocated_ip', 'division', 'district', 'thana', 'address', 'client_mobile', 'client_email', 'selling_price_bdt_excluding_vat')
        );
        foreach ($getdata as $key => $value) {
            $arr = array(
                'Home', //client_type
                // $value->service ?? 'PPPoE', //Service
                'Wired', //connection_type
                $value->full_name, //client_name
                'PoP', //bandwidth_distribution_point
                'Shared', //connectivity_type
                date('d/m/Y', strtotime($value->connection_date)),
                // date_format($value->connection_date, 'd/m/Y'), //activation_date
                $value->package ? $value->package->bandwidth : '5 MB', //bandwidth_allocation
                $value->username, //allocated_ip
                $value->zone && $value->zone->upazila &&  $value->zone->upazila->district && $value->zone->upazila->district->division ? $value->zone->upazila->district->division->name : '', //division
                $value->zone && $value->zone->upazila &&  $value->zone->upazila->district && $value->zone->upazila->district  ? $value->zone->upazila->district->name : '', //district
                $value->zone && $value->zone->upazila ? $value->zone->upazila->name : '', //thana
                $value->address, //address
                $value->phone, //client_mobile
                $value->email, //client_email
                $value->package ? $value->package->price : '', //selling_price_bdt_excluding_vat
            );
            $arraydata[] = $arr;
        }
        $customData = new Collection($arraydata);
        notify()->success('BTRC Report Download Successfully');
        $name = 'btrc-report-' . Date::now()->format('d-M-Y H:i a') . '.xlsx';
        return Excel::download(new BTRCDataExport($customData), "$name");
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_invoice(Request $request)
    {
        if (!auth()->user()->can('BTRC Report Export')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

        $data = Invoice::with('customer', 'manager', 'package')->select(
            'invoice_no',
            // 'username',
            'paid_by',
            'invoice_type',
            'manager_id',
            'customer_id',
            'package_id',
            'amount',
            'received_amount',
            'manager_id',
            'invoice_for',
            'updated_at',
        )->when($request->manager, function ($q) use ($request) {
            if ($request->manager !== '---Select Manager--') {
                return $q->where('manager_id', $request->manager);
            }
        })->when($request->search_query, function ($q) use ($request) {
            $searchQuery = '%' . $request->search_query . '%';
            return $q->where('invoice_no', 'LIKE', '%' . $searchQuery . '%')
                ->orWhere('amount', 'LIKE', $searchQuery)
                ->orWhere('invoice_for', 'LIKE', $searchQuery)
                ->orWhereHas('customer', function ($query) use ($searchQuery) {
                    $query->where('username', 'Like', '%' . $searchQuery . '%');
                })
                ->orWhereHas('manager', function ($query) use ($searchQuery) {
                    $query->where('name', 'Like', '%' . $searchQuery . '%');
                });
        });


        // check if payment method selsect
        if ($request->payment_method) $data = $data->where('paid_by', $request->payment_method);

        if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
            $data = $data->where('manager_for', APP_MANAGER);
        } else {
            $data =    $data->where('manager_id', auth()->user()->id);
        };
        $items = ($request->item && $request->item == 'all') ? Customer::select('id')->count() : ($request->item && $request->item !== 'all' ? $request->item : 15);

        $data =  $data->orderBy('updated_at', 'desc')->paginate($items);
        return view("content.report.btrc.payment-invoice", compact('data'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function expense_report(Request $request)
    {

        if (!auth()->user()->can('BTRC Report Export')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

        // $data = Invoice::with('customer', 'manager', 'package')->select(
        //     'invoice_no',
        //     // 'username',
        //     'paid_by',
        //     'invoice_type',
        //     'manager_id',
        //     'customer_id',
        //     'package_id',
        //     'amount',
        //     'received_amount',
        //     'manager_id',
        //     'invoice_for',
        //     'updated_at',
        // )->when($request->manager, function ($q) use ($request) {
        //     return $q->where('manager_id', $request->manager);
        // })
        //     ->where(['invoice_type' => INVOICE_TYPE_EXPENCE, 'status' => STATUS_PAID])
        //     ->when($request->search_query, function ($q) use ($request) {
        //         $searchQuery = '%' . $request->search_query . '%';
        //         return $q->where('invoice_no', 'LIKE', '%' . $searchQuery . '%')
        //             ->orWhere('amount', 'LIKE', $searchQuery)
        //             ->orWhere('invoice_for', 'LIKE', $searchQuery)
        //             ->orWhereHas('customer', function ($query) use ($searchQuery) {
        //                 $query->where('username', 'Like', '%' . $searchQuery . '%');
        //             })
        //             ->orWhereHas('manager', function ($query) use ($searchQuery) {
        //                 $query->where('name', 'Like', '%' . $searchQuery . '%');
        //             });
        //     });

        // if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
        //     $data = $data->where('manager_for', APP_MANAGER);
        // } else {
        //     $data =    $data->where('manager_id', auth()->user()->id);
        // };


        $data = DailyExpense::when($request->manager, function ($q) use ($request) {
            if ($request->manager !== 'Select Manager') {
                return $q->where('manager_id', $request->manager);
            }
        })->when($request->search_query, function ($q) use ($request) {
            $searchQuery = '%' . $request->search_query . '%';
            return $q->where('invoice_no', 'LIKE', '%' . $searchQuery . '%')
                ->orWhere('amount', 'LIKE', $searchQuery)
                ->orWhere('invoice_for', 'LIKE', $searchQuery)
                ->orWhereHas('customer', function ($query) use ($searchQuery) {
                    $query->where('username', 'Like', '%' . $searchQuery . '%');
                })
                ->orWhereHas('manager', function ($query) use ($searchQuery) {
                    $query->where('name', 'Like', '%' . $searchQuery . '%');
                });
        });
        if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
            $data = $data->where('manager_for', APP_MANAGER);
        } else {
            $data =  $data->where('manager_id', auth()->user()->id);
        }

        // check if payment method selsect
        if ($request->payment_method) $data = $data->where('method', $request->payment_method);

        $data =  $data->orderBy('updated_at', 'desc')->paginate(get_paginate($request, 'invoices'));
        return view("content.report.btrc.expense-report", compact('data'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function download_pdf(Request $request)
    {

        if (!auth()->user()->can('BTRC Report Export')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $data = Invoice::with('customer', 'manager', 'package')->select(
            'invoice_no',
            // 'username',
            'paid_by',
            'invoice_type',
            'manager_id',
            'customer_id',
            'package_id',
            'amount',
            'received_amount',
            'manager_id',
            'invoice_for',
            'updated_at',
        )->when($request->manager, function ($q) use ($request) {
            return $q->where('manager_id', $request->manager);
        })->when($request->search_query, function ($q) use ($request) {
            $searchQuery = '%' . $request->search_query . '%';
            return $q->where('invoice_no', 'LIKE', '%' . $searchQuery . '%')
                ->orWhere('amount', 'LIKE', $searchQuery)
                ->orWhere('invoice_for', 'LIKE', $searchQuery)
                ->orWhereHas('customer', function ($query) use ($searchQuery) {
                    $query->where('username', 'Like', '%' . $searchQuery . '%');
                })
                ->orWhereHas('manager', function ($query) use ($searchQuery) {
                    $query->where('name', 'Like', '%' . $searchQuery . '%');
                });
        });

        if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
            $data = $data->where('manager_for', APP_MANAGER);
        } else {
            $data =    $data->where('manager_id', auth()->user()->id);
        };
        $data =  $data->orderBy('updated_at', 'desc')->paginate();
        return view("content.report.btrc.payment-invoice", compact('data'));
    }
}
