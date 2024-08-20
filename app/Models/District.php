<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class District extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName) => class_basename(get_called_class()) . " has been {$eventName} id is  $this->id")
            ->logOnlyDirty();
    }
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function upazila()
    {
        return $this->hasMany(Upazila::class);
    }
}
