<?php

use App\Http\Controllers\account\Account;
use App\Http\Controllers\ActivityLogHistoryController;
use App\Http\Controllers\backup\DownloadDatabaseBackup;
use App\Http\Controllers\billing\Billing;
use App\Http\Controllers\Cornjob\ScheduleSendSmsController;
use App\Http\Controllers\customer\Customer;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\manager\PermissionController;
use App\Http\Controllers\mikrotik\MikrotikController;
use App\Http\Controllers\SentmessageController;
use App\Http\Controllers\settings\SystemSettingController;
use App\Http\Controllers\sms\SmsGroupController;
use App\Http\Controllers\UpazilaController;
use App\Http\Controllers\customer\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportImportController;
use App\Http\Controllers\Exports\PDFExportController;
use App\Http\Controllers\manager\Manager;
use App\Http\Controllers\manager\ManagerUser;
use App\Http\Controllers\mikrotik\Mikrotik;
use App\Http\Controllers\network\Network;
use App\Http\Controllers\OltController;
use App\Http\Controllers\OnuController;
use App\Http\Controllers\package\Package;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\sms\SMS;
use App\Http\Controllers\sms\SmsTamplateController;
use App\Http\Controllers\Ticket\TicketCategoryController;
use App\Http\Controllers\Ticket\TicketController;
use App\Models\Customer as ModelsCustomer;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//👉 MENU: cache Clear
Route::get('tst', function () {
    $jsonFilePath = public_path('assets/json/search-vertical.json');
    // Path to the JSON file
    // $jsonFilePath = 'data.json';

    // Read JSON file
    $jsonData = file_get_contents($jsonFilePath);
    // New member object to be added
    // Decode JSON data into PHP array
    $dataArray = json_decode($jsonData, true);
    $customers = ModelsCustomer::select(
        'id',
        'full_name',
        'username',
        'avater',
        'customer_for',
        'manager_id'
    )->get();

    $newMember = [];
    foreach ($customers as $key => $cmr_item) {
        if ($cmr_item) {
            $newMember[] = array(
                "id" => $cmr_item->id,
                "name" => $cmr_item->full_name,
                "subtitle" => $cmr_item->username,
                "src" => $cmr_item->avater,
                "url" => "user/customer-user/$cmr_item->id",
                "manager_id" => $cmr_item->manager_id,
                "customer_for" => $cmr_item->customer_for,
            );
        }
    }

    $dataArray['members'] = $newMember;
    $jsonData = json_encode($dataArray, JSON_PRETTY_PRINT);
    file_put_contents($jsonFilePath, $jsonData);

    // Print or use the data
    dd($dataArray);
})->name('dc');
//👉 MENU: cache Clear



//👉 MENU: Storage link
Route::get('s-l', function () {
    Artisan::call('storage:link');
})->name('storage-link');
Route::get('rc', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    notify()->success("Cache Clear Successfully");
    //add sarchable data in Lohin Controller
    $login_cotroller = new  LoginController();
    $login_cotroller->searchableData();
    return back();
})->name('rc');

//👉 MENU: authorization
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    require __DIR__ . '/theme.php';
    // MENU: SETTINGS
    Route::resource('settings', SystemSettingController::class);
    Route::get('payment-methods/bkash', function () {
        $data = null;
        return view('content.payment-method.bkash', compact('data'));
    })->name('payment-method.bkash');

    // MENU: Network
    Route::get('get-zonewise-subzone/{id}', [Network::class, 'get_zonewise_subzone'])->name('get_zonewise_subzone');
    Route::get('network-delete-sub-zone/{id}', [Network::class, 'network_delete_sub_zone'])->name('network-delete-sub-zone');
    Route::post('get-zone-wise-subzone', [Network::class, 'get_zone_wise_subzone'])->name('get-zone-wise-subzone');
    Route::resource('network/division', DivisionController::class)->names('division');
    Route::resource('network/district', DistrictController::class)->names('district');
    Route::resource('network/thana', UpazilaController::class)->names('thana');
    Route::resource('network/olt', OltController::class);
    Route::resource('network/onu', OnuController::class);
    Route::get('fttx/olt-details/{id}', [OltController::class, 'olt_no_of_pon_port']);  
    
    // MENU: Managers
    Route::resource('managers/log-history', ActivityLogHistoryController::class);
    Route::get('download-backup-file', [ActivityLogHistoryController::class, 'doanload_file'])->name('activity-log');
    Route::resource('role/permission', PermissionController::class);
    Route::get('managers-franchise_panel_balance_invoice', [Manager::class, 'franchise_panel_balance_invoice'])->name('activity-log');

    // 👉 MENU: SMS
    Route::resource('sms/sms-group', SmsGroupController::class);
    Route::resource('sms/sms-report', SentmessageController::class);
    Route::resource('sms/sms_templates', SmsTamplateController::class);
    Route::get('check-balance', [SMS::class, 'check_balance'])->name('check_balance');

    //👉 MENU: Customer , User
    Route::resource('user/customer-user', CustomerController::class);
    Route::get('user/expire-customer', [CustomerController::class, 'expire_customer'])->name('expire_customer');
    Route::get('user/disable-customer/{id}', [CustomerController::class, 'disable_customer'])->name('disable-customer');
    Route::get('user/mktonline-user', [CustomerController::class, 'mikrotik_online_user'])->name('user-online-mikrotik_online_user');
    Route::put('set-new-user-in-mikrotik/{id}', [CustomerController::class, 'setNewUserInMikrotik'])->name('set-new-user-in-mikrotik');
    Route::put('custome-change-password/{id}', [Customer::class, 'customerChangePassword'])->name('customerChangePassword');
    Route::get('invoice-add/{id}', [CustomerController::class, 'add_invoice'])->name('add_invoice');
    Route::get('user/mikrotik-import-user', [CustomerController::class, 'mikrotik_import_users'])->name('mikrotik_import_users');
    Route::put('update-customer-package/{id}', [Customer::class, 'update_customer_package'])->name('update-customer-package');
    Route::get('confirm-customer/{id}', [CustomerController::class, 'confirm_payment'])->name('confirm_payment');
    Route::get('customer-change-package/{id}', [Customer::class, 'change_package'])->name('customer_change_package_get');
    Route::put('customer-req-chage-package/{id}', [CustomerController::class, 'change_package_cal'])->name('change_package_cal');
    Route::post('mkt-pendingcustomer-assign-franchise', [CustomerController::class, 'mkt_pendingcustomer_assign_franchise'])->name('mkt_pendingcustomer_assign_franchise');
    Route::get('mikrotik-online-disconnect/{id}', [CustomerController::class, 'mikrotik_online_disconnect'])->name('mikrotik-online-disconnect');
    Route::get('customer-grace/{id}', [CustomerController::class, 'customer_grace_page'])->name('customer_grace_page');
    Route::put('customer-grace/{id}', [CustomerController::class, 'customer_allow_grace'])->name('customer_allow_grace');
    Route::get('customer_delete/{id}', [CustomerController::class, 'customer_delete'])->name('customer_delete');
    Route::get('customer-suspended/{id}', [CustomerController::class, 'customer_suspended'])->name('customer-suspended');
    Route::post('disabled-multiple-customer', [CustomerController::class, 'desabled_multiple_customer'])->name('desabled_multiple_customer');
    Route::get('users/delete-users', [Customer::class, 'get_all_delete_customers'])->name('delete-customers');
    Route::get('users/grace-users', [CustomerController::class, 'grace_user_list'])->name('grace_user_list');
    Route::get('customer-delete-permanently/{id}', [CustomerController::class, 'customer_delete_permanently'])->name('customer_delete_permanently');
    Route::post('customer-bulk-grace', [CustomerController::class, 'customer_bulk_grace'])->name('customer_bulk_grace');
    Route::post('bulk-renew-customer-expire-date', [CustomerController::class, 'bulk_renew_customer_expire_date'])->name('bulk_renew_customer_expire_date');
    Route::get('user-change-expire-date/{id}', [CustomerController::class, 'user_change_expire_date'])->name('user-change-expire-date');
    Route::put('user-change-expire-date/{id}', [CustomerController::class, 'user_change_expire_date_put'])->name('user-change-expire-date-put');
    Route::post('save-customer-note', [CustomerController::class, 'save_customer_note'])->name('save_customer_note');
    Route::delete('delete-customer-note/{id}', [CustomerController::class, 'delete_customer_note'])->name('delete_customer_note');

    //👉 MENU: Invoice
    Route::resource('billing/invoice', InvoiceController::class);
    Route::get('billing/receive-invoices', [InvoiceController::class, 'receive_invoice'])->name('billing-receive-invoice');
    Route::get('billing/refund-invoices', [InvoiceController::class, 'refund_invoice'])->name('billing-refund-invoice');
    Route::get('billing/print-invoice/{id}', [InvoiceController::class, 'printInvoice'])->name('billing-print-invoice');
    Route::put('invoice-payment/{id}', [InvoiceController::class, 'invoice_payment'])->name('invoice_payment');
    Route::get('billing/invoice/add-inv-package-info-user/{id}', [InvoiceController::class, 'add_inv_package_info_user'])->name('add_inv_package_info_user');
    Route::get('invoice-payment/{id}', [InvoiceController::class, 'invoice_payment_get'])->name('invoice_payment_get');
    Route::put('invoice-refund/{id}', [InvoiceController::class, 'invoice_refund'])->name('invoice-refund');
    Route::get('show-invoice', [InvoiceController::class, 'showInvoice'])->name('showInvoice');
    Route::post('user/store-invoice', [Billing::class, 'storeInvoice'])->name('user-store-invoice');
    Route::post('invoice/export-pdf', [InvoiceController::class, 'exportPDF'])->name('user-store-export_pdf');

    //👉 MENU: Ticket
    Route::resource('ticketcategory', TicketCategoryController::class);
    Route::resource('ticket', TicketController::class);
    Route::get('get-customer-ticket/{id}', [TicketController::class, 'get_customer_ticket']);

  

    //👉 MENU: Account
    Route::get('account/category/delete/{id}', [Account::class, 'accountCategoryDelete'])->name('accountCategoryDelete');
    Route::get('export-income-expense-report', [Account::class, 'export_income_expense_report'])->name('export-income-expense-report');
    Route::put('dailyincome/update/{id}', [Account::class, 'updateDailyIncome'])->name('updateDailyIncome');
    Route::put('updateDailyExpense/update/{id}', [Account::class, 'updateDailyExpense'])->name('updateDailyExpense');
    Route::get('dailyincome/delete/{id}', [Account::class, 'dailyIncomeDelete'])->name('dailyIncomeDelete');
    Route::get('dailyexpence/delete/{id}', [Account::class, 'dailyExpenceDelete'])->name('dailyExpenceDelete');
    Route::get('account/summary', [Account::class, 'account_summary'])->name('account-summary');
    Route::get('account/monthly_accounts', [Account::class, 'monthly_accounts'])->name('monthly_accounts');

    //👉 MENU: Mikrotik & Package 
    Route::get('mikrotik-package/{id}', [Package::class, 'mikroTikPackage']);
    Route::get('package-delete/{id}', [Package::class, 'package_delete'])->name('package.delete');

    // 👉 MENU: Manager 
    Route::put('managers-add-balance/{id}', [Manager::class, 'managers_add_balance'])->name('managers-add-balance');
    Route::get('manager-balance-transfer/{id}', [Manager::class, 'manager_balance_transfer_get'])->name('manager-balance-transfer.get');
    Route::get('edit-manager/{id}', [Manager::class, 'editManager'])->name('editManager');
    Route::get('view-transfer-balance/{id}', [Manager::class, 'view_transfer_balance'])->name('view_transfer_balance');
    Route::get('accept-transfer-balance/{id}', [Manager::class, 'accept_transfer_balance'])->name('accept_transfer_balance');
    Route::put('managers-balance-transfer/{id}', [Manager::class, 'manager_balance_transfer_put'])->name('manager-balance-transfer.put');
    Route::get('managers-ledger', [Manager::class, 'managers_ladger'])->name('managers-ledger');
    Route::get('rejacte-managers-balance-transfer/{id}', [Manager::class, 'rejacte_managers_balance_transfer'])->name('rejacte_managers_balance_transfer');
    Route::get('seen-transfer-balance-notification/{id}', [Manager::class, 'seen_transfer_balance_notification'])->name('seen_transfer_balance_notification');
    Route::get('manager-profile/{id}', [Manager::class, 'managerProfile'])->name('managerProfile');
    Route::get('user-change-password', [ManagerUser::class, 'userChangePassword'])->name('userChangePassword');
    Route::get('user-change', [ManagerUser::class, 'userChange'])->name('userChange');
    Route::get('user-dashboard', [ManagerUser::class, 'userDashboard'])->name('userDashboard');
    Route::get('user-notes', [ManagerUser::class, 'userNotes'])->name('userNotes');
    Route::get('user-invoice', [ManagerUser::class, 'userInvoice'])->name('userInvoice');
    Route::get('user-package', [ManagerUser::class, 'userPackage'])->name('userPackage');
    Route::get('user-ticket', [ManagerUser::class, 'userTicket'])->name('userTicket');
    Route::put('manager-change-password/{id}', [Manager::class, 'managerChangePassword'])->name('managerChangePassword');
    Route::put('manager-user-change-password/{id}', [ManagerUser::class, 'managerUserChangePassword'])->name('managerUserChangePassword');
    Route::put('update_profile/{id}', [Manager::class, 'update_profile'])->name('update_profile');
    Route::put('franchise_add_custom_pkg_price/{id}', [Manager::class, 'franchise_add_custom_pkg_price'])->name('franchise_add_custom_pkg_price');
    Route::get('manager-create', [Manager::class, 'manager_create'])->name('manager.create');
    Route::get('manager-update-panel-balance/{id}', [Manager::class, 'manager_update_panel_balance'])->name('manager-update-panel-balance');
    Route::get('manager-delete/{id}', [Manager::class, 'manager_delete'])->name('manager_delete');
    Route::get('role_delete/{id}', [Manager::class, 'role_delete'])->name('role_delete');

    //👉 MENU: Mikrotik
    Route::resource('mikrotik', MikrotikController::class);
    Route::get('delete_ip_pool/{id}', [Mikrotik::class, 'delete_ip_pool'])->name('delete_ip_pool');
    Route::get('mikrotik_info/{id}', [Mikrotik::class, 'mikrotik_info'])->name('mikrotik_info');
    Route::get('mikrotik_system_resource/{id}', [Mikrotik::class, 'mikrotik_system_resource'])->name('mikrotik_system_resource');

    // 👉 MENU: Report
    Route::group(['prefix' => '/report'], function () {
        Route::get('btrc', [ReportController::class, 'btrcReport'])->name('reprot.btrc');
        Route::get('btrc-export', [ReportController::class, 'btrcExport'])->name('reprot.btrc-export');
        Route::get('payment-invoice', [ReportController::class, 'payment_invoice'])->name('reprot.payment-invoice');
        Route::get('expense', [ReportController::class, 'expense_report'])->name('reprot.expense_report');
    });

    // 👉 MENU: Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('mini-dashboard', [DashboardController::class, 'mini_dashboard'])->name('mini-dashboard');
    Route::put('mini-dashboard-inv-update/{id}', [DashboardController::class, 'mini_dashboard_inv_update'])->name('mini-dashboard-inv-update');
    Route::get('customer-edit-super-manager/{id}', [DashboardController::class, 'customerEditForSuperManager'])->name('customer-edit-super-manager');
    Route::put('customer-update-super-manager/{id}', [DashboardController::class, 'customerUpdateForSuperManager'])->name('customer-update-super-manager');


    // 👉 MENU: Download PDF
    Route::get('account-summary-pdf', [PDFExportController::class, 'account_summary_pdf'])->name('account-summary-pdf');
    Route::get('download-pdf', [PDFExportController::class, 'download_pdf'])->name('download-pdf');


    //👉 download backup
    Route::get('db-backup', [DownloadDatabaseBackup::class, 'downloadDatabaseBackup'])->name('db-backup');

    // 👉 Export and Import
    Route::get('export-expire-customer', [ExportImportController::class, 'exportExpireCustomer'])->name('export-expire-customer');
    Route::get('user-export', [ExportImportController::class, 'userExport'])->name('user-export');
    Route::post('import-customer', [ExportImportController::class, 'import_customer'])->name('import-customer');
    Route::get('cs', [ScheduleSendSmsController::class, 'index']);
});
