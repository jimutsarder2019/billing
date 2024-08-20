<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerAssignZone extends Model
{
    use HasFactory;
    protected $guarded = [];
    function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
