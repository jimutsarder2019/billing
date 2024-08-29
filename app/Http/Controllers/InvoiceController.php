<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Customer;
use App\Models\CustomerBalanceHistory;
use App\Models\CustomerGraceHistorys;
use App\Models\Invoice;
use App\Models\InvoiceEditHistory;
use App\Models\Manager;
use App\Models\ManagerBalanceHistory;
use App\Models\Package;
use App\Models\SmsTemplates;
use App\Services\ConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class InvoiceController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new Invoice();
        $this->modelName = "Invoice";
        $this->routename = "invoice.index";
        $this->table = "invoices";
        $this->tamplate = "content.invoice";
    }
    /**
     * Display a listing of the resource.
     */
    public function exportPDF($data)
    {
        try {
            $pdf = PDF::loadView('content.pdf.invoice', compact('data'));
            return $pdf->download("invoice.pdf");
            // return view('content.pdf.invoice', compact('data'));
            notify()->success('PDF Download Successfully');
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('Invoice')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                $data = $this->model->with('customer', 'manager', 'franchise_manager')
                    ->when($request->search_query, function ($q) use ($request) {
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
                    })
                    ->when($request->manager, function ($q) use ($request) {
                        return $q->where('manager_id', $request->manager);
                    })
                    ->when($request->invoice_for, function ($q) use ($request) {
                        return $q->where('invoice_for', $request->invoice_for);
                    })
                    ->when($request->status, function ($q) use ($request) {
                        return $q->where('status', $request->status);
                    })
                    // ->latest()->paginate($request->item ?? 10);
                    ->latest()->paginate($request->item ?? 10);
            } else {
                $data = Invoice::with('customer', 'manager', 'franchise_manager')
                    ->when($request->search_query, function ($q) use ($request) {
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
                    })
                    ->when(auth()->user(), function ($q) {
                        if (auth()->user()->type == FRANCHISE_MANAGER) {
                            return $q->where('manager_id', auth()->user()->id);
                        }
                    })
                    ->when($request->invoice_for, function ($q) use ($request) {
                        return $q->where('invoice_for', $request->invoice_for);
                    })
                    ->when($request->status, function ($q) use ($request) {
                        return $q->where('status', $request->status);
                    })
                    ->latest()->paginate($request->item ?? 10);
            }
            if ($request->has('export')) {
                return $this->exportPDF($data);
            } else {
                return view("$this->tamplate.index", compact('data'));
            }
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    //👉 new invoice page 
    function create()
    {
        try {
            $users = Customer::select('id', 'full_name', 'username', 'manager_id', 'customer_for', 'bill', 'discount')
                ->when(auth()->user(), function ($q) {
                    if (auth()->user()->type == FRANCHISE_MANAGER) {
                        return $q->where('manager_id', auth()->user()->id);
                    } else {
                        return $q->where('customer_for', APP_MANAGER);
                    }
                })
                ->get();
            return view('content.invoice.new-invoice', compact('users'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    /**
     *👉 Display a listing of the resource.
     */
    public function receive_invoice(Request $request)
    {
        if (!auth()->user()->can('Received Payments')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                $data = $this->model->with('customer', 'manager')
                    ->when($request->search_query, function ($q) use ($request) {
                        $searchQuery = '%' . $request->search_query . '%';
                        return $q->where('invoice_no', 'LIKE', '%' . $searchQuery . '%')
                            ->orWhere('amount', 'LIKE', $searchQuery);
                    })
                    ->when($request->manager, function ($q) use ($request) {
                        return $q->where('manager_id', $request->manager);
                    })
                    ->when($request->date_range, function ($q) use ($request) {
                        $q->whereBetween('created_at', date_range_search($request->date_range));
                    })
                    ->where(['status' => STATUS_PAID])->latest()->paginate($request->item ?? 10);
            } else {
                $data = Invoice::with('customer', 'manager')
                    ->when($request->search_query, function ($q) use ($request) {
                        $searchQuery = '%' . $request->search_query . '%';
                        return $q->where('full_name', 'LIKE', '%' . $searchQuery . '%')
                            ->orWhere('phone', 'LIKE', $searchQuery);
                    })
                    ->when(auth()->user(), function ($q) {
                        if (auth()->user()->type == FRANCHISE_MANAGER) {
                            return $q->where('manager_id', auth()->user()->id);
                        }
                    })
                    ->where(['status' => STATUS_PAID])->orderBy('updated_at', 'desc')->paginate($request->item ?? 10);
            }
            return view("$this->tamplate.index", compact('data'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    /**
     *👉 Display a listing of the resource.
     */
    public function refund_invoice(Request $request)
    {
        if (!auth()->user()->can('Received Payments')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        try {
            if (auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                $data = $this->model->with('customer', 'manager')
                    ->when($request->search_query, function ($q) use ($request) {
                        $searchQuery = '%' . $request->search_query . '%';
                        return $q->where('invoice_no', 'LIKE', '%' . $searchQuery . '%')
                            ->orWhere('amount', 'LIKE', $searchQuery);
                    })
                    ->when($request->manager, function ($q) use ($request) {
                        return $q->where('manager_id', $request->manager);
                    })
                    ->where(['status' => STATUS_REFUND])->latest()->paginate($request->item ?? 10);
            } else {
                $data = Invoice::with('customer', 'manager')
                    ->when($request->search_query, function ($q) use ($request) {
                        $searchQuery = '%' . $request->search_query . '%';
                        return $q->where('full_name', 'LIKE', '%' . $searchQuery . '%')
                            ->orWhere('phone', 'LIKE', $searchQuery);
                    })
                    ->when(auth()->user(), function ($q) {
                        if (auth()->user()->type == FRANCHISE_MANAGER) {
                            return $q->where('manager_id', auth()->user()->id);
                        }
                    })
                    ->where(['status' => STATUS_REFUND])->orderBy('updated_at', 'desc')->paginate($request->item ?? 10);
            }
            return view("$this->tamplate.refund-invoice", compact('data'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * 👉 Show the form for creating a new resource.
     */
    public function invoice_payment(Request $request, $id)
    {
        if (!auth()->user()->can('Received Payments')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        DB::beginTransaction();
        try {
            $status  = $request->amount > $request->received_amount ? STATUS_DUE : ($request->amount < $request->received_amount ? STATUS_OVER_PAID : STATUS_PAID);
            $data = Invoice::with('customer', 'customer.manager')->find($id);
            if ($data->status == STATUS_PAID)  return error_message('The Invoice is already paid. Please Update it manually', 403);
            if ($data->invoice_for == INVOICE_MANAGER_ADD_PANEL_BALANCE) {
                // dd($request->all());
                $ttl_amount  = $data->received_amount + $request->payable_amount;
                $previous_received_amount =  $data->received_amount;
                $data->update([
                    'received_amount'   => $data->received_amount + $request->payable_amount,
                    'due_amount'        => $data->due_amount - $request->payable_amount,
                    'status'            => ($ttl_amount < $data->amount) ? STATUS_DUE : (($ttl_amount > $data->amount) ? STATUS_OVER_PAID : STATUS_PAID),
                    'paid_by'           => $request->paid_by,
                    'transaction_id'    => $request->transaction_id,
                ]);

                // store to invoice edit history
                InvoiceEditHistory::create([
                    'manager_id'    => auth()->user()->id,
                    'invoice_id'    => $data->id,
                    'invoice_amount' => $data->amount,
                    'previous_received_amount' => $previous_received_amount,
                    'total_received_amount'   => $data->received_amount,
                    'new_received_amount'   => $request->payable_amount,
                    'paid_by'               => $data->paid_by,
                    'transaction_id'        => $data->transaction_id,
                    'status'                => $data->status,
                ]);
            } else {

                if ((($data->customer && $data->customer->manager->type == 'app_manager') && auth()->user()->type == 'app_manager') | ($data->customer && $data->customer->manager->type == 'franchise' && auth()->user()->id == $data->customer->manager->id) | auth()->user()->hasRole(SUPER_ADMIN_ROLE)) {
                    if ($request->has('payable_amount') && $data->status  == STATUS_DUE) {
                        $received_amount = $request->received_amount + $request->payable_amount;
                        $status = $received_amount  == $data->amount ? STATUS_PAID : STATUS_DUE;
                        $due_amount = $data->due_amount - $request->payable_amount;
                        Manager::where('id', Auth::user()->id)->increment('wallet',  $request->payable_amount);
                    } else {
                        $received_amount = $request->received_amount;
                        $due_amount = $status == 'due' ? $request->amount - $request->received_amount : 00;
                        Manager::where('id', Auth::user()->id)->increment('wallet',  $request->received_amount);
                    }

                    // 👉 customer increment balance if customer monthly bill
                    if ($data->invoice_for == INVOICE_CUSTOMER_ADD_BALANCE) {
                        Customer::select('id', 'wallet')->where('id', $data->customer->id)->increment('wallet',  $received_amount);
                        // 👉 customer balance history
                        CustomerBalanceHistory::create([
                            'customer_id'   => $data->customer->id,
                            'manager_id'    => auth()->user()->id,
                            'balance'       => $received_amount,
                            'update_Reasons' => 'Add Balance'
                        ]);
                    }
                    $customer = Customer::with('mikrotik', 'purchase_package', 'zone', 'package')->where('id', $data->customer_id)->first();
                    //👉 update invoice table
                    $previous_received_amount = $data->received_amount;
                    $data->update([
                        'amount'            => $request->amount,
                        'received_amount'   => $received_amount,
                        'customer_pkg_id_when_inv_payment'   => $customer->package_id,
                        'due_amount'        => $due_amount,
                        'status'            => $status,
                        'advanced_amount'   => $status == STATUS_OVER_PAID ? $request->received_amount - $request->amount : 00,
                        'paid_by'           => $request->paid_by,
                        'expire_date'       => $customer->expire_date ?? null,
                        'customer_status'   => $customer->status,
                        'customer_old_expire_date'  => $customer->expire_date,
                        'transaction_id'    => $request->transaction_id,
                        'manager_id'        => Auth::user()->id
                    ]);

                    // store to invoice edit history
                    InvoiceEditHistory::create([
                        'manager_id'    => auth()->user()->id,
                        'invoice_id'    => $data->id,
                        'invoice_amount' => $data->amount,
                        'previous_received_amount' => $previous_received_amount,
                        'total_received_amount'   => $received_amount,
                        'new_received_amount'   => $request->payable_amount,
                        'paid_by'               => $data->paid_by,
                        'transaction_id'        => $data->transaction_id,
                        'status'                => $data->status,
                    ]);

                    //increment manager wallet
                    Manager::where('id', auth()->user()->id)->increment('wallet', $received_amount);
                    // manager balance history 
                    ManagerBalanceHistory::create([
                        'manager_id'    => auth()->user()->id,
                        'invoice_id'    => $data->id,
                        'balance'       => $received_amount,
                        'history_for'   => $data->invoice_for,
                        'franchise_panel_balance' => auth()->user()->wallet + $received_amount,
                        'sign'          => '+',
                        'note'          => $data->comment
                    ]);
                    if ($status !== 'due' && $data->invoice_for == INVOICE_CUSTOMER_MONTHLY_BILL) {
                        if ($customer->allow_grace !== null) {
                            // last grace history
                            $customer_grace_info =  CustomerGraceHistorys::where('customer_id', $customer->id)->latest()->first();
                            // check different customer last grace from todays 
                            $today = Carbon::now();
                            $diffFromToday = $today->diffInDays($customer_grace_info->created_at);
                            if ($customer_grace_info->created_at->format('d-m-y') == Carbon::now()->format('d-m-y')) {
                                if ($customer_grace_info->grace < $customer->allow_grace) {
                                    $expire_date = Carbon::now()->addMonth()->subDays($customer->allow_grace - $customer_grace_info->grace);
                                } else {
                                    $expire_date = Carbon::now()->addMonth();
                                }
                            } else {
                                if ($diffFromToday > $customer->allow_grace) {
                                    $expire_date = Carbon::now()->addMonth()->subDays($customer->allow_grace);
                                } elseif ($diffFromToday < $customer->allow_grace) {
                                    $expire_date = Carbon::now()->addMonth()->subDays($customer->allow_grace - $diffFromToday);
                                } elseif ($diffFromToday == $customer->allow_grace || $diffFromToday == 0) {
                                    $expire_date = Carbon::now()->addMonth();
                                }
                            }
                        } else {
                            $today = Carbon::now();
                            if ($today < $customer->expire_date) {
                                $expire_date = Carbon::parse($customer->expire_date)->addMonth();
                            } else {
                                $expire_date = Carbon::now()->addMonth();
                            }
                        }

                        // 👉 store data customer current expire date
                        $old_expire_date        = $customer->expire_date;
                        $customer_old_status    = $customer->status;
                        // 👉 store data customer current expire date End //
                        $customer->update([
                            'package_id'    => $customer->purchase_package_id,
                            'status'        => CUSTOMER_ACTIVE,
                            'expire_date'   => $expire_date,
                            'is_sms_sent_before_expire' => 0,
                            'allow_grace'   => null,
                            'is_auto_invoice_create' => STATUS_TRUE,
                            'wallet'        => $status == STATUS_OVER_PAID ? $customer->wallet += ($request->received_amount - $request->amount) : $customer->wallet
                        ]);
                        // 👉 update invoice new customer_new_expire_date
                        Invoice::select('id', 'customer_new_expire_date')->find($id)->update(['customer_new_expire_date' => $expire_date]);
                        // 👉 mikrotik connection
                        $connection = new ConnectionService($customer->mikrotik->host, $customer->mikrotik->username, $customer->mikrotik->password, $customer->mikrotik->port);
                        $query_data =  $connection->activeDisconnectedUser($customer, $old_expire_date, $customer_old_status);
                        if (gettype($query_data) == 'string') return error_message($query_data);
                        // 👉 throw msg if has ant error in mirkotik call
                        //👉 check invoice payment sms template
                        $sms_tamp = SmsTemplates::select('id', 'name')->where('type', TMP_INV_PAYMENT)->first();
                        // 👉 return msg if not exists
                        if ($sms_tamp) {
                            // 👉 check customer_for and send sms 
                            if ($customer->customer_for == APP_MANAGER) SendSingleMessage(['template_type' => TMP_INV_PAYMENT, 'customer_id' => $customer->id, 'number' => $customer->phone, 'invoice' => $data]);
                        }
                    }
                } else {
                    return error_message('Invoice Updated Not allow');
                }
            }
            DB::commit();
            notify()->success('invoice Update Successfully');
            return redirect()->route('invoice.index');
        } catch (\Throwable $th) {
            dd($th);
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     *👉 Show the form for invoice-refund a new resource.
     */
    public function invoice_refund(Request $request, $id)
    {
        if ($request->note == null && $request->reasons == null)  return error_message('reasone or note field is require');
        DB::beginTransaction();
        try {
            $inv = Invoice::with('customer', 'customer.mikrotik')->find($id);
            if (!$inv) return error_message('data Not Found');
            $auth = auth()->user();
            $customer = Customer::where('id', $inv->customer->id)->first();
            if ($inv->invoice_for == INVOICE_CUSTOMER_MONTHLY_BILL) {
                //👉 Check package 
                if ($inv->customer_pkg_id_when_inv_payment == null) return error_message('Something want Wrong');
                $check_package = Package::where('id', $inv->customer_pkg_id_when_inv_payment)->where('mikrotik_id', $inv->customer->mikrotik_id)->first();
                if (!$check_package) return error_message('package not found');
                //👉 connect mikrotik
                $connection = new ConnectionService($inv->customer->mikrotik->host, $inv->customer->mikrotik->username, $inv->customer->mikrotik->password, $inv->customer->mikrotik->port);
                //👉 call user disconnect profile
                $request['package'] = $check_package->id . "|" . $check_package->name;
                $request['old_expire_date'] = $inv->customer_old_expire_date;
                $res_query = $connection->changeCustomerPackage($customer, $request);
                if (gettype($res_query) == 'string') return error_message($res_query);
                // save customer data 
                $expire_package = AdminSetting::where('slug', 'disconnect_package')->first();
                if (!$expire_package) return error_message('disconnect Package Not Found');
                $find_expire_package = Package::select('id', 'name')->where('name', $expire_package->value)->first();
                if (!$find_expire_package) return error_message('Package not found');

                $customer->status       = ($inv->customer_status !== null ? $inv->customer_status : ($find_expire_package->id == $inv->package_id ? CUSTOMER_EXPIRE : CUSTOMER_ACTIVE));
                $customer->expire_date  = $inv->customer_old_expire_date;
                $customer->package_id   = $inv->customer_pkg_id_when_inv_payment;
                $customer->save();
                //👉 disconnect user profile 
            } elseif ($inv->invoice_for == INVOICE_CUSTOMER_ADD_BALANCE) {
                $customer->decrement('wallet', $inv->received_amount);
                // $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id
            }
            $manager = Manager::select('id', 'panel_balance', 'wallet', 'type')->where('id', $customer->customer_for == FRANCHISE_MANAGER ? $customer->manager_id : $auth->id)->first();
            if ($customer->customer_for == FRANCHISE_MANAGER)  $manager->increment('panel_balance', $inv->received_amount);
            $manager->decrement('wallet', $inv->received_amount);
            $inv->update([
                'received_amount'   => 00,
                'due_amount'        => 00,
                'advanced_amount'   => 00,
                'status'            => STATUS_REFUND,
                'comment'           => $request->reasons ?? $request->note,
            ]);
            notify()->success('Invoice Refund Successfully');
            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     * 👉 Show the form for creating a new resource.
     */
    public function invoice_payment_get($id)
    {
        try {
            $data = $this->model->with('manager', 'customer')->find($id);
            // dd();
            return view('content.invoice.invoice-payment', compact('data'));
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }

    /**
     *👉 Store a newly created resource in storage.
     * $id is user id 
     */
    public function add_inv_package_info_user($id)
    {
        $customer = Customer::with('package')->find($id);
        if (auth()->user()->type == FRANCHISE_MANAGER) {
            $bill = franchise_actual_packge_price(auth()->user()->id, $customer->package_id);
            $bill = $bill - $customer->discount;
        } else {
            $bill = $customer->package->price - $customer->discount;
        }
        return response()->json(['bill' => $bill]);
    }

    /**
     *👉 Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = $this->model->with('customer', 'manager', 'invoice_edit_history', 'invoice_edit_history.manager', 'franchise_manager')->find($id);
            return view('content.invoice.preview-invoice', compact('data'));
        } catch (\Throwable $th) {
        }
    }
    /**
     * 👉Display the specified resource.
     */
    public function printInvoice(string $id)
    {
        try {
            $data = $this->model->with('customer', 'manager', 'invoice_edit_history', 'invoice_edit_history.manager')->find($id);
            return view('content.invoice.print-invoice', compact('data'));
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
        }
    }

    /**
     * 👉Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE) || !auth()->user()->can('Invoice Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data = Invoice::with('customer')->find($id);
            return view('content.invoice.superadmin-update-invoice', compact('data'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    /**
     * 👉Show the form for editing the specified resource.
     */
    public function showInvoice(Request $request)
    {
        // dd($request->all());
        try {
            if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE) || !auth()->user()->can('Invoice Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data = Invoice::with('customer')->where('id', $request->inv_id)->first();
            return view('content.invoice.superadmin-update-invoice', compact('data'));
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }

    /**
     * 👉Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            if (!auth()->user()->hasRole(SUPER_ADMIN_ROLE) || !auth()->user()->can('Invoice Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
            $data = Invoice::find($id);
            $data->update([
                'amount' => $request->amount ?? $data->amount,
                'received_amount' => $request->received_amount ?? $data->received_amount,
                'due_amount' => $request->due_amount ?? $data->due_amount,
                'paid_by' => $request->paid_by ?? $data->paid_by,
                'status' => $request->status ?? $data->status,
                'transaction_id' => $request->transaction_id ?? $data->transaction_id,
            ]);
            notify()->success('Update Successfully');
            return back();
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
            return back();
        }
    }

    /**
     *👉 Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
