<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Manager;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Dashboard' => [],
            'Managers' => [
                'Managers View',  //check to change vertical manue
                'Managers Add',
                'Managers Edit',
                'Managers View Profile',
                'Managers Delete',
                'Managers Assign Role',
                'Managers Add Custom Balance',
                'Managers Balance Transfer',
                'managers-ledger',
                'Managers change-password',
            ],
            'Auth Manager' => [
                'Auth Manager Profile',
                'Auth Manager Clear Cache',
                'Auth Manager User Disconnect',
            ],
            'Role' => [
                'Role Add',
                'Role Edit',
                'Role Delete',
                'Role Assign Permission',
            ],

            'Activity Log' => [
                'Activity Log Auth',
                'Activity Log All',
            ],

            'User' => [
                'View User',
                'Add User',
                'User Edit',
                'User Delete',
                'Pending User',
                'New Registration User',
                'Mikrotik Import User',
                'Expired User',
                'Grace User',
                'MikroTik Online User',
                'User Change Package',
                'Schedule Package Change',
                'Change Package Regular Extend Method',
                'Change Package Custom Expire Date',
                'User Add Balance',
                'User Change Password',
                'User Allow Grace',
                'User Create Invoice',
                'User disconnect',
                'Delete Users',
                'Confirm Payment',
                'Users MikrotTik Status Change', //update date march 2 2024
                'User Change expire date', //added date fab 3 2024
                'New Customer Discount',
                'Edit Customer Discount',
            ],
            'Mikrotik' => [
                'Mikrotik View',
                'Mikrotik Add',
                'Mikrotik Edit',
                'Mikrotik Delete',
                'Mikrotik Sync',
            ],
            'Mikrotik IP Pool' => [
                'Mikrotik IP Pool View',
                'Mikrotik IP Pool Add',
                'Mikrotik IP Pool Edit',
                'Mikrotik IP Pool Delete',
            ],
            'Packages' => [
                'View Package',
                'Add Package',
                'Packages Edit',
                'Packages Delete',
            ],
            'Network' => [],
            'Divisions' => [
                'Divisions Add',
                'Divisions Edit',
                'Divisions Delete',
            ],
            'Districts' => [
                'Districts Add',
                'Districts Edit',
                'Districts Delete',
            ],
            'Thana' => [
                'Thana Add',
                'Thana Edit',
                'Thana Delete',
            ],
            'Zone' => [
                'Zone Add',
                'Zone Edit',
                'Zone Delete',
            ],
            'Sub-Zone' => [
                'Sub-Zone Add',
                'Sub-Zone Edit',
                'Sub-Zone Delete',
            ],
            'OLT' => [
                'OLT Add',
                'OLT Edit',
                'OLT Delete',
            ],
            'ONU' => [
                'ONU Add',
                'ONU Edit',
                'ONU Delete',
            ],
            'Ticket' => [],
            'Ticket Category' => [
                'Ticket Category Add',
                'Ticket Category Edit',
                'Ticket Category Delete',
            ],
            'Ticket' => [
                'Ticket Add',
                'Ticket Edit',
                'Ticket Change Status',
                'Ticket Delete',
            ],
            'Account' => [],
            'Account-Category' => [
                'Account-Category Add',
                'Account-Category Edit',
                'Account-Category Delete',
            ],

            'Daily-Income' => [
                'Daily-Income Add',
                'Daily-Income Edit',
                'Daily-Income Delete',
            ],
            'Daily-Expenses' => [
                'Daily-Expenses Add',
                'Daily-Expenses Edit',
                'Daily-Expenses Delete',
                'Summary',
            ],
            'Billing' => [
                'Invoice',
                'Invoice Add',
                'Invoice Edit',
                'Invoice Delete',
                'Received Payments',
                'Invoice Payments',
                'Refund Payments',
                'Custom Payments',
                'Invoice Details',
                'Invoice Print',
            ],
            'SMS' => [
                'SMS Api view',
                'SMS Api Add',
                'SMS Api Edit',
                'SMS Api Delete',
                'SMS Send sms',
                'SMS Report',
            ],
            'SMS Template' => [
                'SMS Template Add',
                'SMS Template Edit',
                'SMS Template Delete',
            ],
            'Settings' => [
                'Settings View',
                'Settings edit',
                'Settings db-backup',
            ],
            'Report' => [
                'BTRC Report',
                'BTRC Report Export',
            ],
            'Mini Dashboard' => [
                'update_customer_info'
            ],
        ];
        $role   = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $faker  = Factory::create();
        $user   = Manager::create(['type' => 'app_manager', 'name' => 'user', 'email' => 'user@gmail.com', 'phone' => $faker->phoneNumber(), 'password' => Hash::make('123456'),],);
        foreach ($permissions as $k => $permission) {
            Permission::create(['name' => $k, 'group_name' => $k,    'guard_name' => 'web']);
            foreach ($permission as $index => $item) {
                Permission::create(['name' => $item, 'group_name' => $k, 'guard_name' => 'web']);
            }
        }
        $role->syncPermissions(Permission::all());
        $user->assignRole($role);
    }
}
