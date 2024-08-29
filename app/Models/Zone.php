<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Zone extends Model
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
    public function sub_zone()
    {
        return $this->hasMany(SubZone::class, 'zone_id');
    }
    public function upazila()
    {
        return $this->hasOne(Upazila::class, 'id');
    }
    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
    public function manager()
    {
        return $this->hasMany(Manager::class);
    }
}
