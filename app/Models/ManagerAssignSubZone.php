<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerAssignSubZone extends Model
{
    use HasFactory;
    protected $guarded = [];
    function subzone()
    {
        return $this->belongsTo(SubZone::class, 'subzone_id');
    }
}
