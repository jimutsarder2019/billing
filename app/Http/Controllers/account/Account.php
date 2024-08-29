<?php

namespace App\Http\Controllers\account;

use App\Exports\IncomeExpenceReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountCategory;
use App\Models\BillCollection;
use App\Models\Customer;
use App\Models\Manager;
use App\Models\DailyIncome;
use App\Models\DailyExpense;
use App\Models\Invoice;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class Account extends Controller
{
    public $per_page_item = 10;
    /**
     * 👉 Display the specified resource. to show accounts category
     */
    public function viewCategory()
    {
        if (!auth()->user()->can('Account-Category')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);

        $categories = AccountCategory::withCount('dailyIncome', 'dailyExpanse')
            ->when(auth()->user(), function ($q) {
                if (auth()->user()->type == FRANCHISE_MANAGER) {
                    return $q->where('manager_id', auth()->user()->id);
                } else {
                    return $q->where('manager_for', APP_MANAGER);
                }
            })->get();
        return view('content.account.category.view-category', compact('categories'));
    }

    /**
     * 👉 Store a newly created resource in storage Account create category
     */
    public function storeCategory(Request $request)
    {
        if (!auth()->user()->can('Account-Category Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $validate = Validator::make($request->all(), [
            'name' => 'required|unique:account_categories,name',
            'type' => 'required'
        ]);
        AccountCategory::create([
            'name' => $request->name,
            'type' => $request->type,
            'manager_id' => Auth::user()->id,
            'manager_for' => auth()->user()->type ?? APP_MANAGER,
            'status' => true
        ]);
        notify()->success('Created Succesfully');
        return back();
    }


    /**
     * 👉 Remove the specified resource from Account category.
     */
    function accountCategoryDelete($id)
    {
        if (!auth()->user()->can('Account-Category Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            $data = AccountCategory::with('dailyExpanse', 'dailyIncome')->find($id);
            if ($data->upazila->count() > 0 | $data->customer->count() > 0 | $data->sub_zone->count() > 0) return error_message('data cannot be delete');
            $data->delete();
            notify()->success('Delete Succesfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * 👉 Update the specified resource in storage.
     */
    public function updateCategory(Request $request, $id)
    {
        if (!auth()->user()->can('Account-Category Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
            'type' => 'required'
        ]);

        $category = AccountCategory::find($id);
        if ($request->status == 'on') {
            $status = true;
        } else {
            $status = false;
        }

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'status' => $status
        ]);
        notify()->success('Update Succesfully');
        return back();
    }


    /**
     * 👉 Display the specified resource.
     */



    public function viewBillCollection()
    {
        if (!auth()->user()->can('Bill-Collection')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $collections = BillCollection::all();
        $customers = Customer::all();
        $managers = Manager::all();
        return view('content.account.bill-collection.view-bill-collection', compact('collections', 'customers', 'managers'));
    }

    /**
     * 👉 Display the specified Customers resource.
     */
    public function customerDetails(Request $request)
    {
        $selected_customer = Customer::where('id', $request->customer)->first();
        return response()->json(['customer' => $selected_customer]);
    }

    /**
     * 👉 Store a newly created resource in storage.
     */
    public function storeBillCollection(Request $request)
    {
        if (!auth()->user()->can('Bill-Collection Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'customer'      => 'required',
            'customer_name' => 'required',
            'method'        => 'required',
            'monthly_bill'  => 'required',
            'received'      => 'required',
            'manager'       => 'required',
            'issue_date'    => 'required',
            'note'          => 'required'
        ]);

        $bill = BillCollection::create([
            'customer_name' => $request->customer_name,
            'customer_id' => $request->customer,
            'invoice_no' => time(),
            'method' => $request->method,
            'monthly_bill' => $request->monthly_bill,
            'received_amount' => $request->received,
            'manager_id' => $request->manager,
            'issue_date' => $request->issue_date,
            'note' => $request->note
        ]);

        $customer = Customer::where('id', $bill->customer_id)->first();
        $package = $customer->package()->first();
        $expire_date = strtotime($customer->billing_date) + $package->validdays;
        $expire_date_formatted = gmdate("Y-m-d", $expire_date);

        $invoice = Invoice::create([
            'user_id'       => $bill->customer_id,
            'invoice_no'    => $bill->invoice_no,
            'invoice_for'   => 'monthly_bill',
            'package_id'    => $bill->customer->package_id,
            'zone_id'       => $bill->customer->zone_id,
            'sub_zone_id'   => 1,
            'expire_date'   => $expire_date_formatted,
            'amount'        => $bill->monthly_bill,
            'received_amount'   => $bill->received_amount,
            'paid_by'           => $bill->method,
            'transaction_id'    => $bill->transaction_id,
            'manager_id'        => Auth::user()->id,
            'comment'           => 'invoice for monthly bill'
        ]);

        if ($bill->monthly_bill > $bill->received_amount) {
            $due = $bill->monthly_bill - $bill->received_amount;
            $invoice->due_amount = $due;
            $invoice->status = 'due';
            $invoice->save();
        } else if ($bill->monthly_bill < $bill->received_amount) {
            $advanced = $bill->received_amount - $bill->monthly_bill;
            $invoice->advanced_amount = $advanced;
            $invoice->status = 'over_paid';
            $invoice->save();
        } else {
            $invoice->status = 'paid';
            $invoice->save();
        }
        if ($request->add_wallet_balance == 'on') {
            $customer->wallet = ($customer->wallet - $bill->received_amount);
            $customer->billing_date = $expire_date_formatted;
            $customer->save();
        } else {
            $customer->billing_date = $expire_date_formatted;
            $customer->save();
        }
        notify()->success('Create Succesfully');
        return back();
    }

    /**
     * 👉 Display the specified resource.
     */
    public function viewDailyIncome(Request $request)
    {

        $userType = auth()->user()->type;
        $userId = $request->manager ?? auth()->user()->id;

        if ($userType == FRANCHISE_MANAGER) {
            // dd($userId);
            $data = Invoice::with('customer', 'manager')
                ->select(
                    'id',
                    'invoice_no',
                    'manager_for',
                    DB::raw('null as service_name'),
                    DB::raw('invoice_for as category_id'),
                    'invoice_for',
                    'amount',
                    'paid_by as method',
                    'transaction_id',
                    'received_amount',
                    'customer_id',
                    'created_at',
                    'updated_at',
                    DB::raw('created_at as date'),
                    'status',
                    'manager_id',
                    DB::raw('comment as description'),
                )
                ->where(['manager_id' => $userId, 'status' => STATUS_PAID])
                ->where('invoice_for', '!=', INVOICE_MANAGER_ADD_PANEL_BALANCE)
                ->when($request, function ($q) use ($request) {
                    if ($request->date_range) {
                        return $q->whereBetween('updated_at', date_range_search($request->date_range));
                    } else {
                        return $q->whereDate('updated_at', DB::raw('CURDATE()'));
                    }
                })
                ->when($request->invoice_for, function ($q) use ($request) {
                    return $q->where('invoice_for', $request->invoice_for);
                })
                ->when($request, function ($q) use ($request) {
                    if (!$request->has('invoice_for')) {
                        $q->union(
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
                                    DB::raw('null as invoice_for'),
                                    'created_at',
                                    'updated_at',
                                    'date',
                                    'status',
                                    'manager_id',
                                    'description',
                                )
                                ->where('manager_id', $request->manager ?? auth()->user()->id)
                                ->when($request, function ($q) use ($request) {
                                    if ($request->date_range) {
                                        return $q->whereBetween('date', date_range_search($request->date_range));
                                    } else {
                                        return $q->whereDate('date', DB::raw('CURDATE()'));
                                    }
                                })

                        );
                    }
                });
        } else {
            // dd($userId);
            $data = Invoice::with('customer', 'manager')
                ->select(
                    'id',
                    'invoice_type',
                    'invoice_no',
                    'manager_for',
                    DB::raw('null as service_name'),
                    DB::raw('invoice_for as category_id'),
                    'invoice_for',
                    'amount',
                    'paid_by as method',
                    'transaction_id',
                    'received_amount',
                    'customer_id',
                    'created_at',
                    'updated_at',
                    DB::raw('created_at as date'),
                    'status',
                    'manager_id',
                    DB::raw('comment as description'),
                )
                ->where(['manager_for' => APP_MANAGER, 'invoice_type' => INVOICE_TYPE_INCOME])
                ->whereNotNull('received_amount')
                ->when($request->invoice_for, function ($q) use ($request) {
                    return $q->where('invoice_for', $request->invoice_for);
                })->when($request, function ($q) use ($request) {
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
                            DB::raw('null as invoice_for'),
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

        $total_amount = $data->sum('received_amount');
        $all_data = $data->get();
        // Iterate over each payment method to calculate count and sum
        $card_data = collect(PAYMENT_METHOD_ITEMS)->map(function ($method) use ($all_data) {
            return [
                'method' => $method,
                'count' => $all_data->whereIn('method', [$method, strtolower($method)])->count(),
                'sum' => $all_data->whereIn('method', [$method, strtolower($method)])->sum('received_amount'),
            ];
        })->keyBy('method')->toArray();


        $incomes =  $data->latest()->paginate($request->item ?? 10);

        $categories = AccountCategory::when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('manager_id', auth()->user()->id);
            } else {
                return $q->where('manager_for', APP_MANAGER);
            }
        })->where('type', 'Income')->get();

        if ($request->has('export') && $request->export !== null) {
            $request['export_data'] = $incomes;
            return $this->export_income_expense_report($request);
            return redirect()->route('account-daily-income');
        } else {
            return view('content.account.daily-income.view-daily-income', compact(
                'total_amount',
                'incomes',
                'categories',
                'card_data'
            ));
        }
    }

    /**
     *👉 Display the specified resource.
     */

    public function storeDailyIncome(Request $request)
    {
        if (!auth()->user()->can('Daily-Income Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'amount' => 'required',
            'method' => 'required',
            'date' => 'required',
            'description' => 'required'
        ]);

        $vouchar = time();
        DailyIncome::create([
            'service_name' => $request->name,
            'category_id' => $request->category,
            'amount' => $request->amount,
            'method' => $request->method,
            'date' => format_ex_date($request->date),
            'description' => $request->description,
            'manager_id' => auth()->user()->id,
            'manager_for' => auth()->user()->type ?? APP_MANAGER,
            'vouchar_no' => $vouchar,
            'transaction_id' => $request->transaction
        ]);
        balance_increment($request->amount);
        notify()->success('Create Succesfully');
        return back();
    }

    /**
     * 👉 Update the specified resource in storage.
     */
    public function updateDailyIncome(Request $request, $id)
    {
        if (!auth()->user()->can('Daily-Income Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name'      => 'required',
            'category'  => 'required',
            'amount'    => 'required',
            'method'    => 'required',
            'date'      => 'required',
            'description' => 'required'
        ]);

        $vouchar = time();
        DailyIncome::find($id)->update([
            'service_name'  => $request->name,
            'category_id'   => $request->category,
            'amount'        => $request->amount,
            'method'        => $request->method,
            'date'          => $request->date,
            'description'   => $request->description,
            'manager_id'    => Auth::user()->id,
            'vouchar_no'    => $vouchar,
            'transaction_id' => $request->transaction
        ]);
        balance_increment($request->amount);
        notify()->success('Update Succesfully');
        return back();
    }

    /**
     * 👉 Update the specified resource in storage.
     */
    public function monthly_accounts(Request $request)
    {
        if (!auth()->user()->can('Daily-Expenses')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $year = $request->year ?? Carbon::now()->year;
        $auth_user = auth()->user();
        //  ========= yearly Report =========
        for ($month = 1; $month <= 12; $month++) {
            // Set the date to the first day of the month
            $startDate = Carbon::create($year, $month, 1)->toDateString();
            // Set the date to the last day of the month
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
            $userType = auth()->user()->type;
            $userId = $request->manager ?? auth()->user()->id;
            if ($userType == FRANCHISE_MANAGER) {
                // dd($userId);
                $income_inv = Invoice::select('invoice_type', 'manager_id', 'updated_at', 'status', 'received_amount')
                    ->where(['manager_id' => $auth_user->id, 'invoice_type' => INVOICE_TYPE_INCOME, 'status' => STATUS_PAID])
                    ->whereBetween('updated_at', [$startDate, $endDate])->sum('received_amount');
                $daily_income =  DailyIncome::with('manager')
                    ->select('manager_for', 'amount', 'date', 'status',)
                    ->where(['manager_for' => APP_MANAGER])
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');

                $expense_inv = Invoice::select('invoice_type', 'manager_id', 'updated_at', 'status', 'received_amount',)
                    ->where(['manager_id' => $auth_user->id, 'invoice_type' => INVOICE_TYPE_EXPENCE, 'status' => STATUS_PAID])
                    ->orWhere(['invoice_for' => INVOICE_MANAGER_ADD_PANEL_BALANCE, 'manager_id' => $auth_user->id])
                    ->whereBetween('updated_at', [$startDate, $endDate])->sum('received_amount');

                $daily_expense =  DailyExpense::select('manager_id', 'amount', 'date', 'status',)
                    ->where(['manager_id' => $auth_user->id])
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');
                $date = new DateTime($endDate);
                $f_data = [
                    'month' => $date->format('F'),
                    'income' => $income_inv + $daily_income,
                    'expense' => $expense_inv + $daily_expense,
                ];
                $data[] = $f_data;
            } else {
                $income_inv = Invoice::select('invoice_type', 'manager_for', 'updated_at', 'status', 'received_amount')
                    ->where(['manager_for' => APP_MANAGER, 'invoice_type' => INVOICE_TYPE_INCOME])
                    ->whereNotNull('received_amount')
                    ->whereBetween('updated_at', [$startDate, $endDate])->sum('received_amount');

                $daily_income =  DailyIncome::with('manager')
                    ->select('manager_for', 'amount', 'date', 'status',)
                    ->where(['manager_for' => APP_MANAGER])
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');
                $expense_inv = Invoice::select('invoice_type', 'manager_for', 'updated_at', 'status', 'received_amount',)
                    ->where(['manager_for' => APP_MANAGER, 'invoice_type' => INVOICE_TYPE_EXPENCE, 'status' => STATUS_PAID])
                    ->whereBetween('updated_at', [$startDate, $endDate])->sum('received_amount');
                $daily_expense =  DailyExpense::with('manager')
                    ->select('manager_for', 'amount', 'date', 'status',)
                    ->where(['manager_for' => APP_MANAGER])
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');
                $date = new DateTime($endDate);
                $f_data = [
                    'month' => $date->format('F'),
                    'income' => $income_inv + $daily_income,
                    'expense' => $expense_inv + $daily_expense,
                ];
                $data[] = $f_data;
            }
        }

        if ($request->has('is_return')) {
            return $data;
        } else {
            return view(
                'content.account.report.monthly_accounts',
                compact('data')
            );
        }
    }

    /**
     * 👉 Remove the specified resource from storage.
     */
    function dailyIncomeDelete($id)
    {
        if (!auth()->user()->can('Daily-Income Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            DailyIncome::find($id)->delete();
            notify()->success('delete successfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     *👉 Display the specified resource.
     */
    public function viewDailyExpense(Request $request)
    {
        $userType = auth()->user()->type;
        $userId = auth()->user()->id;
        if ($userType == FRANCHISE_MANAGER) {
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
                    'franchise_manager_id',
                    'customer_id',
                    'created_at',
                    'updated_at',
                    DB::raw('created_at as date'),
                    'status',
                    'manager_id',
                    DB::raw('comment as description'),
                )
                ->when($request, function ($q) use ($request) {
                    if (auth()->user()->type == FRANCHISE_MANAGER) {
                        $q->where(['franchise_manager_id' => auth()->user()->id]);
                    } else {
                        $q->where(['manager_id' => $userId, 'status' => STATUS_PAID]);
                    }
                })
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
                            DB::raw('null as franchise_manager_id'),
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
        $total_amount = $data->sum('received_amount');
        $expenses =  $data->latest()->paginate($request->item ?? 10);

        $all_data = $data->get();

        // Iterate over each payment method to calculate count and sum
        $card_data = collect(PAYMENT_METHOD_ITEMS)->map(function ($method) use ($all_data) {
            return [
                'method' => $method,
                'count' => $all_data->whereIn('method', [$method, strtolower($method)])->count(),
                'sum' => $all_data->whereIn('method', [$method, strtolower($method)])->sum('received_amount'),
            ];
        })->keyBy('method')->toArray();

        $categories = AccountCategory::select('id','name', 'type', 'manager_id', 'manager_for', 'status')->when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('manager_id', auth()->user()->id);
            } else {
                return $q->where('manager_for', APP_MANAGER);
            }
        })->where('type', 'Expense')->get();
        if ($request->has('export') && $request->export !== null) {
            $request['export_data'] = $expenses;
            return $this->export_income_expense_report($request);
            return redirect()->route('account-daily-income');
        } else {
            return view(
                'content.account.daily-expense.view-daily-expense',
                compact(
                    'expenses',
                    'categories',
                    'total_amount',
                    'card_data'
                )
            );
        }
    }
    /**
     * 👉 Store a newly created resource in storage.
     */    public function storeDailyExpense(Request $request)
    {
        if (!auth()->user()->can('Daily-Expenses Add')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name'      => 'required',
            'category'  => 'required',
            'amount'    => 'required',
            'method'    => 'required',
            'date'      => 'required',
            'description' => 'required'
        ]);
        try {
            $vouchar = time();

            DailyExpense::create([
                'expense_claimant' => $request->name,
                'category_id' => $request->category,
                'amount' => $request->amount,
                'method' => $request->method,
                'date' => format_ex_date($request->date),
                'description' => $request->description,
                'manager_id' => auth()->user()->id,
                'manager_for' => auth()->user()->type ?? APP_MANAGER,
                'vouchar_no' => $vouchar,
                'transaction_id' => $request->transaction
            ]);
            balance_decrement($request->amount);
            notify()->success('Create Succesfully');
            return back();
        } catch (\Throwable $th) {
            dd($th);
        }
    }


    /**
     * 👉 Update the specified resource in storage.
     */

    public function updateDailyExpense(Request $request, $id)
    {
        if (!auth()->user()->can('Daily-Expenses Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'amount' => 'required',
            'method' => 'required',
            'date' => 'required',
            'description' => 'required'
        ]);
        DailyExpense::find($id)->update([
            'expense_claimant' => $request->name,
            'category_id' => $request->category,
            'amount' => $request->amount,
            'method' => $request->method,
            'date' => $request->date,
            'description' => $request->description,
            'transaction_id' => $request->transaction
        ]);
        notify()->success('Create Succesfully');
        return back();
    }

    /**
     * 👉 Remove the specified resource from storage.
     */
    function dailyExpenceDelete($id)
    {
        if (!auth()->user()->can('Daily-Expenses Delete')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            DailyExpense::find($id)->delete();
            notify()->success('Delete Successfully');
            return back();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    /**
     *👉 Display and calculate the specified resource.
     */
    function account_summary(Request $request)
    {
        if (!auth()->user()->can('Summary')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
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
            return view('content.account.summary', compact(
                'total_daily_income_amount',
                'expenses',
                'incomes',
            ));
        } catch (\Throwable $th) {
            // 👉 throw $th;
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     * request parameter
     * export = string | income or expenses
     * export_data = array 
     *👉 export_income_expense_report the specified resource.
     */
    function export_income_expense_report(Request $request)
    {
        if ($request->export == 'income') {
            $arraydata = array(array('SL', 'Date', 'Voucher or Inv No', 'User Name', 'Category', 'Service Name', 'Amount', 'Method', 'TRX ID', 'Manager',));
            foreach ($request->export_data as $index => $item) {
                $username = null;
                if ($item->customer_id) {
                    $customer = Customer::where('id', $item->customer_id)->first();
                    if ($customer) $username = $customer->username;
                }
                $category_name = null;
                if ($item->category_id) {
                    $category = AccountCategory::select('id', 'name')->where('id', $item->category_id)->first();
                    if ($category) $category_name = $category->name;
                }
                $arr = array(
                    $index + 1, //Sl
                    $item->date ?? $item->date, //Date
                    $item->vouchar_no ?? $item->invoice_no, //INV No
                    $item->customer_id ? $username : '', //username
                    $item->category_id ? $category_name : 'N/A', //category
                    $item->service_name, //service_name 
                    $item->amount, //amount
                    $item->method, //method
                    $item->transaction_id, //transaction_id
                    $item->manager ? $item->manager->name : "", //Manager
                );
                $arraydata[] = $arr;
            }
        } else {
            $arraydata = array(array('SL', 'Date', "Voucher or Inv No", 'User Name', 'Category', 'expense_claimant', 'Amount', 'Method', 'TRX ID', 'Manager',));
            foreach ($request->export_data as $index => $item) {
                $username = null;
                if ($item->customer_id) {
                    $customer = Customer::where('id', $item->customer_id)->first();
                    if ($customer) $username = $customer->username;
                }
                $category_name = null;
                if ($item->category_id) {
                    $category = AccountCategory::select('id', 'name')->where('id', $item->category_id)->first();
                    if ($category) {
                        $category_name = $category->name;
                    }
                }
                $arr = array(
                    $index + 1, //Sl
                    $item->date ?? $item->date, //Date
                    $item->vouchar_no ?? $item->invoice_no, //INV No
                    $item->customer_id ? $username : '', //username
                    $item->category_id ? $category_name : 'N/A', //category
                    $item->expense_claimant, //expense_claimant 
                    $item->amount, //amount
                    $item->method, //method
                    $item->transaction_id, //transaction_id
                    $item->manager ? $item->manager->name : "", //Manager
                );
                $arraydata[] = $arr;
            }
        }

        $customData = new Collection($arraydata);
        notify()->success('Download Successfully');
        $name = "$request->export-" . Date::now()->format('d-M-Y') . '.xlsx';
        return Excel::download(new IncomeExpenceReport($customData), "$name");
    }
}
