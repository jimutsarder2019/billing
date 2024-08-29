<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Mikrotik extends Model
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



    // many customer 
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    // many manager 
    public function managers()
    {
        return $this->hasMany(Manager::class);
    }
    // many Packages 
    public function packages()
    {
        return $this->hasMany(Package::class);
    }
    // many ippools 
    public function ippools()
    {
        return $this->hasMany(IPpool::class);
    }
}
