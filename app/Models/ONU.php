<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ONU extends Model
{
    use HasFactory;
    protected $guarded = [];

    function customer()
    {
        return $this->belongsTo(Customer::class)->select('id', 'username', 'full_name');
    }
    function zone()
    {
        return $this->belongsTo(Zone::class)->select('id', 'name');
    }
    function sub_zone()
    {
        return $this->belongsTo(Zone::class)->select('id', 'name');
    }
    function olt()
    {
        return $this->belongsTo(OLT::class);
    }
}
