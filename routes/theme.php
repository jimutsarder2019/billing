<?php

use App\Http\Controllers\sms\SMS;
use Illuminate\Support\Facades\Route;

//👉 controller path
$controller_path = 'App\Http\Controllers';
Route::get('mikrotik/add-mirotik', $controller_path . '\mikrotik\Mikrotik@addMikrotik')->name('mikrotik-add-mikrotik');
Route::post('mikrotik/store-mirotik', $controller_path . '\mikrotik\Mikrotik@storeMikrotik')->name('mikrotik-store-mikrotik');
Route::get('mikrotik/view-mirotik', $controller_path . '\mikrotik\Mikrotik@viewMikrotik')->name('mikrotik-view-mikrotik');
Route::get('mikrotik/edit-mirotik/{id}', $controller_path . '\mikrotik\Mikrotik@editMikrotik')->name('mikrotik-edit-mikrotik');
Route::put('mikrotik/update-mirotik/{id}', $controller_path . '\mikrotik\Mikrotik@updateMikrotik')->name('mikrotik-update-mikrotik');
Route::get('mikrotik/sync-mikrotik/{id}', $controller_path . '\mikrotik\Mikrotik@addToRadius')->name('mikrotik-sync-mikrotik');
Route::get('mikrotik/ip-pool', $controller_path . '\mikrotik\Mikrotik@showMikrotikIpPool')->name('mikrotik-ip-pool');
Route::post('mikrotik/save-ip-pool', $controller_path . '\mikrotik\Mikrotik@saveMikrotikIpPool')->name('mikrotik-save-ip-pool');
Route::get('mikrotik/edit-ip-pool/{id}', $controller_path . '\mikrotik\Mikrotik@editMikrotikIpPool')->name('mikrotik-edit-ip-pool');
Route::put('mikrotik/update-ip-pool/{id}', $controller_path . '\mikrotik\Mikrotik@updateMikrotikIpPool')->name('mikrotik-update-ip-pool');

//👉 MENU: PACKAGES
Route::get('packages/add-package', $controller_path . '\package\Package@addPackage')->name('packages-add-package');
Route::post('packages/store-package', $controller_path . '\package\Package@storePackage')->name('packages-store-package');
Route::get('packages/view-package', $controller_path . '\package\Package@viewPackage')->name('packages-view-package');
Route::get('packages/edit-package/{id}', $controller_path . '\package\Package@editPackage')->name('packages-edit-package');
Route::put('packages/update-package/{id}', $controller_path . '\package\Package@updatePackage')->name('packages-update-package');

// 👉 MENU: NETWORK
Route::get('network/add-zone', $controller_path . '\network\Network@addZone')->name('network-add-zone');
Route::post('network/store-zone', $controller_path . '\network\Network@storeZone')->name('network-store-zone');
Route::get('network/view-zone', $controller_path . '\network\Network@viewZone')->name('network-view-zone');
Route::get('network/zone/{id}', $controller_path . '\network\Network@deleteZone')->name('network-delete-zone');
Route::get('network/edit-zone/{id}', $controller_path . '\network\Network@editZone')->name('network-edit-zone');
Route::put('network/update-zone/{id}', $controller_path . '\network\Network@updateZone')->name('network-update-zone');
Route::get('network/add-sub-zone', $controller_path . '\network\Network@addSubZone')->name('network-add-sub-zone');
Route::post('network/store-sub-zone', $controller_path . '\network\Network@storeSubZone')->name('network-store-sub-zone');
Route::get('network/view-sub-zone', $controller_path . '\network\Network@viewSubZone')->name('network-view-sub-zone');
Route::get('network/edit-sub-zone/{id}', $controller_path . '\network\Network@editSubZone')->name('network-edit-sub-zone');
Route::put('network/update-sub-zone/{id}', $controller_path . '\network\Network@updateSubZone')->name('network-update-sub-zone');
Route::get('network/add-olt', $controller_path . '\network\Network@addOLT')->name('network-add-olt');
Route::post('network/store-olt', $controller_path . '\network\Network@storeOLT')->name('network-store-olt');
Route::get('network/add-onu', $controller_path . '\network\Network@addONU')->name('network-add-onu');
Route::get('network/olt-details/{id}', $controller_path . '\network\Network@oltDetailsForAddOnu')->name('network-olt-details');
Route::post('network/store-onu', $controller_path . '\network\Network@storeOnu')->name('network-store-onu');

//👉 MENU: USER
Route::get('user/add-user', $controller_path . '\customer\Customer@addCustomer')->name('user-add-customer');
Route::post('user/store-user', $controller_path . '\customer\Customer@storeCustomer')->name('user-store-customer');
Route::get('user/get-package-details/{id}', $controller_path . '\customer\Customer@getPackageDetails')->name('user-get-package-details');
Route::get('customer-user/get-package-details/{id}', $controller_path . '\customer\Customer@getPackageDetails');
Route::get('user/view-user', $controller_path . '\customer\Customer@viewCustomer')->name('user-view-user');
Route::get('user/pending-user', $controller_path . '\customer\Customer@pendingCustomer')->name('user-pending-customer');
Route::post('user/approve-user/{id}', $controller_path . '\customer\Customer@approveCustomer')->name('user-approve-customer');
Route::get('user/edit-user/{id}', $controller_path . '\customer\Customer@editCustomer')->name('user-edit-customer');
Route::put('user/update-user/{id}', $controller_path . '\customer\Customer@updateCustomer')->name('user-update-customer');

Route::put('user-change/{id}', $controller_path . '\manager\ManagerUser@userChangeProfile')->name('user-change-profile');

Route::get('user/edit-mikrotik-user/{id}', $controller_path . '\customer\Customer@editMikrotikCustomer')->name('user-edit-mikrotik-customer');
Route::post('user/store-mikrotik-user', $controller_path . '\customer\Customer@storeMikrotikCustomer')->name('user-store-mikrotik-customer');
Route::get('user/view-user/disconnect-expired-user', $controller_path . '\customer\Customer@send_sms_before_customer_expire')->name('user-disconnect-expired-customer');

//👉 MENU: MANAGERS
Route::get('managers/manager-list', $controller_path . '\manager\Manager@listManagers')->name('managers-manager-list');
Route::post('managers/store-manager', $controller_path . '\manager\Manager@storeManager')->name('managers-store-manager');
Route::put('managers/update-manager/{id}', $controller_path . '\manager\Manager@updateManager')->name('managers-update-manager');
Route::get('managers/roles', $controller_path . '\manager\Manager@listRoles')->name('managers-role-list');
Route::post('managers/store-role', $controller_path . '\manager\Manager@storeRole')->name('managers-store-roll');
Route::put('managers/update-role/{id}', $controller_path . '\manager\Manager@updateRole')->name('managers-update-roll');
Route::post('managers/assign_permission/{id}', $controller_path . '\manager\Manager@assignPermission')->name('managers-assign-permission');
Route::post('managers/add-role-to-manager/{id}', $controller_path . '\manager\Manager@addRoleToManager')->name('managers-add-role-to-manager');

// 👉 MENU: ACCOUNTS
Route::get('accounts/category', $controller_path . '\account\Account@viewCategory')->name('account-category');
Route::post('accounts/store-category', $controller_path . '\account\Account@storeCategory')->name('account-store-category');
Route::put('accounts/update-category/{id}', $controller_path . '\account\Account@updateCategory')->name('account-update-category');
Route::get('accounts/bill-collection', $controller_path . '\account\Account@viewBillCollection')->name('account-bill-collection');
Route::post('accounts/bill-collection/get-details/', $controller_path . '\account\Account@customerDetails')->name('account-customer-details');
Route::post('accounts/store-bill-collection', $controller_path . '\account\Account@storeBillCollection')->name('account-store-bill-collection');
Route::get('accounts/daily-incomes', $controller_path . '\account\Account@viewDailyIncome')->name('account-daily-income');
Route::post('accounts/store-daily-income', $controller_path . '\account\Account@storeDailyIncome')->name('account-store-daily-income');
Route::get('accounts/daily-expense', $controller_path . '\account\Account@viewDailyExpense')->name('account-daily-expenses');
Route::post('accounts/store-daily-expense', $controller_path . '\account\Account@storeDailyExpense')->name('account-store-daily-expense');

//👉 MENU: SMS
Route::get('sms/sms-api', $controller_path . '\sms\SMS@viewSMSApi')->name('sms-sms-api');
Route::post('sms/store-sms-api', $controller_path . '\sms\SMS@storeSMSApi')->name('sms-store-sms-api');
Route::post('sms/store-sms-template', $controller_path . '\sms\SMS@storeSMSTemplate')->name('sms-store-sms-template');
Route::get('sms/send-sms', $controller_path . '\sms\SMS@createSendSms')->name('sms-send-sms');

// 👉 get data by sms group 
Route::get('sms/send-sms/group', [SMS::class, 'get_user_by_group'])->name('sms-by--sms');
Route::post('sms/save-group', $controller_path . '\sms\SMS@storeGroup')->name('sms-save-group');
Route::post('sms/get-sms-group-users', $controller_path . '\sms\SMS@getGroupUsers')->name('get-sms-group-users');
Route::post('sms/send-sms', $controller_path . '\sms\SMS@sendSms')->name('send-sms');

// 👉 Main Page Route
Route::get('/', $controller_path . '\dashboard\Analytics@index')->name('dashboard-analytics');
Route::get('/dashboard/analytics', $controller_path . '\dashboard\Analytics@index')->name('dashboard-analytics');
Route::get('/dashboard/crm', $controller_path . '\dashboard\Crm@index')->name('dashboard-crm');
Route::get('/dashboard/ecommerce', $controller_path . '\dashboard\Ecommerce@index')->name('dashboard-ecommerce');

//👉 locale
Route::get('lang/{locale}', $controller_path . '\language\LanguageController@swap');

//👉 layout
Route::get('/layouts/collapsed-menu', $controller_path . '\layouts\CollapsedMenu@index')->name('layouts-collapsed-menu');
Route::get('/layouts/content-navbar', $controller_path . '\layouts\ContentNavbar@index')->name('layouts-content-navbar');
Route::get('/layouts/content-nav-sidebar', $controller_path . '\layouts\ContentNavSidebar@index')->name('layouts-content-nav-sidebar');
Route::get('/layouts/horizontal', $controller_path . '\layouts\Horizontal@index')->name('dashboard-analytics');
Route::get('/layouts/vertical', $controller_path . '\layouts\Vertical@index')->name('dashboard-analytics');
Route::get('/layouts/without-menu', $controller_path . '\layouts\WithoutMenu@index')->name('layouts-without-menu');
Route::get('/layouts/without-navbar', $controller_path . '\layouts\WithoutNavbar@index')->name('layouts-without-navbar');
Route::get('/layouts/fluid', $controller_path . '\layouts\Fluid@index')->name('layouts-fluid');
Route::get('/layouts/container', $controller_path . '\layouts\Container@index')->name('layouts-container');
Route::get('/pages/misc-under-maintenance', $controller_path . '\pages\MiscUnderMaintenance@index')->name('pages-misc-under-maintenance');

//👉 icons
Route::get('/icons/tabler', $controller_path . '\icons\Tabler@index')->name('icons-tabler');
Route::get('/icons/font-awesome', $controller_path . '\icons\FontAwesome@index')->name('icons-font-awesome');

//👉 form layouts
Route::get('/form/layouts-vertical', $controller_path . '\form_layouts\VerticalForm@index')->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', $controller_path . '\form_layouts\HorizontalForm@index')->name('form-layouts-horizontal');
Route::get('/form/layouts-sticky', $controller_path . '\form_layouts\StickyActions@index')->name('form-layouts-sticky');

//👉 form wizards
Route::get('/form/wizard-icons', $controller_path . '\form_wizard\Icons@index')->name('form-wizard-icons');
