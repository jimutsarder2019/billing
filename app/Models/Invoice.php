<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName) => class_basename(get_called_class()) . " has been {$eventName} id is  $this->id")
            ->logOnlyDirty();
    }

    // with manager 
    public function manager()
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }
    // relation with customer 
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // relation with package 
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    //invoice_edit_history
    public function invoice_edit_history()
    {
        return $this->hasMany(InvoiceEditHistory::class);
    }
    
    //invoice_edit_history
    public function incomecategory()
    {
        return $this->belongsTo(AccountCategory::class, 'id', 'category_id');
    }
    //invoice_edit_history
    public function franchise_manager()
    {
        return $this->belongsTo(Manager::class, 'franchise_manager_id');
    }
}
