<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BillCollection extends Model
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
    protected $fillable = [
        'customer_name',
        'customer_id',
        'invoice_no',
        'method',
        'monthly_bill',
        'received_amount',
        'manager_id',
        'transaction_id',
        'issue_date',
        'note'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function manager(){
        return $this->belongsTo(Manager::class, 'manager_id');
    }
}
