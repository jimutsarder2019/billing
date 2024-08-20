<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AccountCategory extends Model
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
    public function dailyIncome()
    {
        return $this->hasMany(DailyIncome::class, 'category_id', 'id',);
    }
    public function dailyExpanse()
    {
        return $this->hasMany(DailyExpense::class, 'category_id', 'id',);
    }
}
