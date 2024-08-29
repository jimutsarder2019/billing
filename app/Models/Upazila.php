<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Upazila extends Model
{
    use HasFactory;
    protected $guarded = [''];
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName) => class_basename(get_called_class()) . " has been {$eventName} id is  $this->id")
            ->logOnlyDirty();
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
    public function sub_zone()
    {
        return $this->hasMany(SubZone::class, 'id');
    }
    public function manager()
    {
        return $this->hasMany(Manager::class, 'id');
    }
    public function customer()
    {
        return $this->hasMany(Customer::class, 'id');
    }
}
