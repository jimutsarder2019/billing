<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PppUser extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function mikrotik()
    {
        return $this->belongsTo(Mikrotik::class, 'mikrotik_id');
    }
    public function manager()
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }
}
