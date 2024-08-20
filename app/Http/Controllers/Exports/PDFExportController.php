<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\DailyExpense;
use App\Models\DailyIncome;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PDFExportController extends Controller
{
    public $per_page_item = 10;
    /**
     * Display a listing of the resource.
     */
    public function account_summary_pdf(Request $request)
    {
        if (!auth()->user()->can('Summary')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $req_date_range = $request->date_range ?? null;
        $req_date_range = $request->date_range;
        try {

            /* =====================* 
            *
            * 👉 daily Expense model data
            *
            *=========================  */
            $userType = auth()->user()->type;
            $userId = auth()->user()->id;
            if ($userType == FRANCHISE_MANAGER) {
                // dd($userId);
                $data = Invoice::with('customer', 'manager')
                    ->select(
                        'id',
                        'invoice_no',
                        'manager_for',
                        DB::raw('null as expense_claimant'),
                        DB::raw('null as category_id'),
                        'amount',
                        'paid_by as method',
                        'transaction_id',
                        'received_amount',
                        'customer_id',
                        'created_at',
                        'updated_at',
                        DB::raw('null as date'),
                        'status',
                        'manager_id',
                        DB::raw('comment as description'),
                    )
                    ->where(['manager_id' => $userId, 'status' => STATUS_PAID])
                    ->when($request, function ($q) use ($request) {
                        if ($request->date_range) {
                            return $q->whereBetween('updated_at', date_range_search($request->date_range));
                        } else {
                            return $q->whereDate('updated_at', DB::raw('CURDATE()'));
                        }
                    })
                    ->union(
                        DailyExpense::with('manager')
                            ->select(
                                'id',
                                DB::raw('null as invoice_no'),
                                'manager_for',
                                'expense_claimant',
                                'category_id',
                                'amount',
                                'method',
                                'transaction_id',
                                'amount as received_amount',
                                DB::raw('null as customer_id'),
                                'created_at',
                                'updated_at',
                                'date',
                                'status',
                                'manager_id',
                                'description',
                            )
                            ->where('manager_id', $userId)
                            ->when($request, function ($q) use ($request) {
                                if ($request->date_range) {
                                    return $q->whereBetween('date', date_range_search($request->date_range));
                                } else {
                                    return $q->whereDate('date', DB::raw('CURDATE()'));
                                }
                            })
                    );
            } else {
                $data = Invoice::with('customer', 'manager')
                    ->select(
                        'id',
                        'invoice_type',
                        'invoice_no',
                        'manager_for',
                        DB::raw('null as expense_claimant'),
                        DB::raw('null as category_id'),
                        'amount',
                        'paid_by as method',
                        'transaction_id',
                        'received_amount',
                        'customer_id',
                        'created_at',
                        'updated_at',
                        DB::raw('null as date'),
                        'status',
                        'manager_id',
                        DB::raw('comment as description'),
                    )
                    ->where(['manager_for' => APP_MANAGER, 'invoice_type' => INVOICE_TYPE_EXPENCE, 'status' => STATUS_PAID])
                    ->when($request, function ($q) use ($request) {
                        if ($request->date_range) {
                            return $q->whereBetween('updated_at', date_range_search($request->date_range));
                        } else {
                            return $q->whereDate('updated_at', DB::raw('CURDATE()'));
                        }
                    })
                    ->union(
                        DailyExpense::with('manager')
                            ->select(
                                'id',
                                DB::raw('null as invoice_type'),
                                DB::raw('null as invoice_no'),
                                'manager_for',
                                'expense_claimant',
                                'category_id',
                                'amount',
                                'method',
                                'transaction_id',
                                'amount as received_amount',
                                DB::raw('null as customer_id'),
                                'created_at',
                                'updated_at',
                                'date',
                                'status',
                                'manager_id',
                                'description',
                            )
                            ->where(['manager_for' => APP_MANAGER])
                            ->when($request, function ($q) use ($request) {
                                if ($request->date_range) {
                                    return $q->whereBetween('date', date_range_search($request->date_range));
                                } else {
                                    return $q->whereDate('date', DB::raw('CURDATE()'));
                                }
                            })
                    );
            }
            $total_expense_amount = $data->sum('received_amount');
            $expenses =  $data->latest()->paginate($request->item ?? 10);

            /* =====================* 
            *
            * 👉 daily income model data
            *
            *=========================  */
            // 👉 income Invoice 
            if ($userType == FRANCHISE_MANAGER) {
                // dd($userId);
                $data = Invoice::with('customer', 'manager')
                    ->select(
                        'id',
                        'invoice_no',
                        'manager_for',
                        DB::raw('null as service_name'),
                        DB::raw('null as category_id'),
                        'amount',
                        'paid_by as method',
                        'transaction_id',
                        'received_amount',
                        'customer_id',
                        'created_at',
                        'updated_at',
                        DB::raw('null as date'),
                        'status',
                        'manager_id',
                        DB::raw('comment as description'),
                    )
                    ->where(['manager_id' => $userId, 'status' => STATUS_PAID])
                    ->when($request, function ($q) use ($request) {
                        if ($request->date_range) {
                            return $q->whereBetween('updated_at', date_range_search($request->date_range));
                        } else {
                            return $q->whereDate('updated_at', DB::raw('CURDATE()'));
                        }
                    })
                    ->union(
                        DailyIncome::with('manager')
                            ->select(
                                'id',
                                DB::raw('null as invoice_no'),
                                'manager_for',
                                'service_name',
                                'category_id',
                                'amount',
                                'method',
                                'transaction_id',
                                'amount as received_amount',
                                DB::raw('null as customer_id'),
                                'created_at',
                                'updated_at',
                                'date',
                                'status',
                                'manager_id',
                                'description',
                            )
                            ->where(['manager_id' => $userId])
                            ->when($request, function ($q) use ($request) {
                                if ($request->date_range) {
                                    return $q->whereBetween('date', date_range_search($request->date_range));
                                } else {
                                    return $q->whereDate('date', DB::raw('CURDATE()'));
                                }
                            })
                    );
            } else {
                // dd($userId);
                $data = Invoice::with('customer', 'manager')
                    ->select(
                        'id',
                        'invoice_type',
                        'invoice_no',
                        'manager_for',
                        DB::raw('null as service_name'),
                        DB::raw('null as category_id'),
                        'amount',
                        'paid_by as method',
                        'transaction_id',
                        'received_amount',
                        'customer_id',
                        'created_at',
                        'updated_at',
                        DB::raw('null as date'),
                        'status',
                        'manager_id',
                        DB::raw('comment as description'),
                    )
                    ->where(['manager_for' => APP_MANAGER, 'invoice_type' => INVOICE_TYPE_INCOME, 'status' => STATUS_PAID])
                    ->when($request, function ($q) use ($request) {
                        if ($request->date_range) {
                            return $q->whereBetween('updated_at', date_range_search($request->date_range));
                        } else {
                            return $q->whereDate('updated_at', DB::raw('CURDATE()'));
                        }
                    })
                    ->union(
                        DailyIncome::with('manager')
                            ->select(
                                'id',
                                DB::raw('null as invoice_type'),
                                DB::raw('null as invoice_no'),
                                'manager_for',
                                'service_name',
                                'category_id',
                                'amount',
                                'method',
                                'transaction_id',
                                'amount as received_amount',
                                DB::raw('null as customer_id'),
                                'created_at',
                                'updated_at',
                                'date',
                                'status',
                                'manager_id',
                                'description',
                            )
                            ->where(['manager_for' => APP_MANAGER])
                            ->when($request, function ($q) use ($request) {
                                if ($request->date_range) {
                                    return $q->whereBetween('date', date_range_search($request->date_range));
                                } else {
                                    return $q->whereDate('date', DB::raw('CURDATE()'));
                                }
                            })
                    );
            }
            $total_daily_income_amount = $data->sum('received_amount');
            $incomes =  $data->latest()->get();
          
            // return view('content.pdf.account-summery', compact('expenses', 'incomes', 'req_date_range'));
            $pdf = PDF::loadView('content.pdf.account-summery', compact('expenses', 'incomes', 'req_date_range'))
                ->setOptions(['defaultFont' => 'sans-serif']);
            notify()->success('PDF Download Successfully');
            $pdf_name = $request->date_range ?? Carbon::now()->format('d-m-Y');
            return $pdf->download("account-summary-$pdf_name.pdf");
        } catch (\Throwable $th) {
            //throw $th;
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
