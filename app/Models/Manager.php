<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class Manager extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName) => class_basename(get_called_class()) . " has been {$eventName} id is  $this->id")
            ->logOnlyDirty();
    }
    protected $guarded = [];
    // relation with zone
    // relation with zone
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
    // relation with assingZones
    public function assingZones()
    {
        return $this->hasMany(ManagerAssignZone::class, 'manager_id');
    }
    // relation with assignedSubzones
    public function assignedSubzones()
    {
        return $this->hasMany(ManagerAssignSubZone::class, 'manager_id');
    }
    // relation with subzones
    public function subzones()
    {
        return $this->hasMany(ManagerAssignSubZone::class, 'manager_id');
    }

    // relation with sub_zone
    public function sub_zone()
    {
        return $this->belongsTo(SubZone::class, 'sub_zone_id');
    }

    // relation with mikrotik
    public function mikrotik()
    {
        return $this->belongsTo(Mikrotik::class, 'mikrotik_id');
    }
    // relation with assignPackage
    public function assignPackage()
    {
        return $this->hasMany(ManagerAssignPackage::class, 'manager_id');
    }
    // relation with managerBalanceHistory
    public function managerBalanceHistory()
    {
        return $this->hasMany(ManagerBalanceHistory::class, 'manager_id');
    }
    // relation with ManagerBalanceTransferHistory
    public function balanceSendHistory()
    {
        return $this->hasMany(ManagerBalanceTransferHistory::class, 'sender_id', 'id');
    }
    // relation with customer
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    // relation with customer
    public function expire_customers()
    {
        return $this->hasMany(Customer::class)->where('status', CUSTOMER_EXPIRE);
    }
    // relation with ManagerBalanceTransferHistory
    public function balanceReciveHistory()
    {
        return $this->hasMany(ManagerBalanceTransferHistory::class, 'reciver_id', 'id');
    }
    // relation with invoice
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    // relation with manager invoice
    public function manager_invoices()
    {
        // return $this->hasMany(Invoice::class)->where('invoice_for', INVOICE_MANAGER_ADD_PANEL_BALANCE);
        return $this->hasMany(Invoice::class);
    }
}
