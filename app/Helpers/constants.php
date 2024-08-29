<?php
const CASH_METHOD =  'Cash';
const BKASH_METHOD =  'Bkash';
const BANK_METHOD =  'Bank Transfer';


const PAYMENT_METHOD_ITEMS = [
    CASH_METHOD,
    BKASH_METHOD,
    BANK_METHOD,
];

// manager profile
const MANAGER_DEFAULT_LOG = "default/default_profile.png";

//user types
const USER_TYPE_ADMIN     = 1;
const USER_TYPE_HOME      = 2;
const USER_TYPE_CORPORATE = 3;
const USER_TYPE_MANAGER   = 4;
const USER_TYPE_AGENT     = 5;

//status
// const STATUS_PENDING    = 0;
const STATUS_SUCCESS    = 1;
const STATUS_SUSPEND    = 2;
const STATUS_DELETE     = 3;
const STATUS_ONLINE     = 4;
const STATUS_OFFLINE    = 5;
const STATUS_EXPIRED    = 6;
const STATUS_ENABLED    = 'Enable';
const STATUS_DISABLE    = 'Disable';

// user Billing status 
const STATUS_UNPAID = 0;



// table status 
const STATUS_TRUE   = 1;
const STATUS_FALSE  = 0;

// invoice status 
const STATUS_PAID   = 'paid';
const STATUS_DUE   = 'due';
const STATUS_OVER_PAID = 'overpaid';

const STATUS_PENDING  = "pending";
const STATUS_PROCESSIMG  = "Processing";
const STATUS_COMPLETE = "Complete";

const STATUS_REFUND  = "refund";
const STATUS_ACCEPTED  = "accepted";
const STATUS_REJECTED  = "rejected";



//admin
const SUPER_ADMIN_ROLE = 'Super Admin';
// franchise, app_manager
const APP_MANAGER = 'app_manager';
const FRANCHISE_MANAGER = 'franchise';

// invoice_TYPE
const INVOICE_TYPE_INCOME = 'income'; //invoice_type  for manager 
const INVOICE_TYPE_EXPENCE = 'expence'; //invoice_type

// invoice_for
const INVOICE_NEW_USER = 'new_user';
const INVOICE_DELETE_CUSTOMER = 'delete_customer';
const INVOICE_CUSTOMER_ADD_BALANCE = 'customer_add_balance';
const INVOICE_CUSTOMER_MONTHLY_BILL = 'monthly_bill';
const INVOICE_MANAGER_ADD_PANEL_BALANCE = 'manager_add_panel_balance';
const INVOICE_MANAGER_RECEIVED = 'receive_invoice';
const INVOICE_CONNECTION_FEE = 'connection_fee';


// managers
const MANAGER_ACCOUNT_ADD_BALANCE_CUSTOMER_WALLET_WHEN_DELETE_CUSTOMER_ACCOUNT = 'manager_account_add_balance_when_delete_customer_account';

// customers
const CUSTOMER_ACTIVE = 'active';
const CUSTOMER_PENDING = 'pending';
const CUSTOMER_APPROVED = 'approved';
const CUSTOMER_SUSPENDED = 'suspended';
const CUSTOMER_NEW_REGISTER = 'new_register';
const CUSTOMER_EXPIRE = 'expire';
const CUSTOMER_DELETE = 'delete';


// sms template
const TMP_WELCOME_SMS  = 'welcome_sms';
const TMP_ACCOUNT_EXPIRE  = 'account_expire';
const TMP_INV_CREATE  = 'invoice_create';
const TMP_PACKAGE_CHANGE  = 'package_change';
const TMP_CUSTOMER_NEW_BALANCE  = 'customer_new_balance';
const TMP_INV_PAYMENT  = 'invoice_payment';
const TMP_CUSTOMER_ACCOUNT_CREATE  = 'customer_account_create';
const TMP_CUSTOMER_INV_AUTO_RENEWABLE  = 'customer_invoice_auto_renewable';
const SEND_SMS_BEFORE_CUSTOMER_EXPIRE  = 'send_sms_before_customer_expire';
const SEND_SMS_UPDATE_EXPIRE_DATE  = 'send_sms_update_expire_date';


// ticket
const TICKET_PENDING = 'pending';
const TICKET_PROCESSING = 'processing';
const TICKET_COMPLETE = 'processing';
