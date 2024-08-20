<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use HasFactory;
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName) => class_basename(get_called_class()) . " has been {$eventName} id is  $this->id")
            ->logOnlyDirty();
    }
    protected $guarded = [];
    //👉  realation with zone
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    //👉  realation with SUb zone
    public function sub_zone()
    {
        return $this->belongsTo(SubZone::class, 'sub_zone_id');
    }

    //👉  realation with package
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    //👉  realation with Mikrotik
    public function mikrotik()
    {
        return $this->belongsTo(Mikrotik::class, 'mikrotik_id');
    }
    //👉  realation with Connection Info
    public function connection_info()
    {
        return $this->hasOne(UserConnectionInfo::class, 'user_id', 'id');
    }
    //👉  realation with invoice
    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'id');
    }
    //👉  realation with Package History
    public function packageHistory()
    {
        return $this->hasMany(CustomerPackageChangeHistory::class, 'customer_id', 'id');
    }
    //👉  realation with Purchese Package
    public function purchase_package()
    {
        return $this->hasOne(Package::class, 'id', 'purchase_package_id');
    }
    //👉  realation with Manager
    public function manager()
    {
        return $this->belongsTo(Manager::class, 'manager_id', 'id');
    }
    //👉  realation with Manager
    public function select_manager()
    {
        return $this->belongsTo(Manager::class, 'manager_id', 'id')->select('id', 'name', 'phone', 'wallet', 'panel_balance');
    }
    //👉  realation with Cutomer Grace
    public function customerGrace()
    {
        return $this->hasMany(CustomerGraceHistorys::class);
    }
    //👉  realation with Cicket
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
