<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerAssignPackage extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
