<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerBalanceHistory extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
    public function app_manager()
    {
        return $this->belongsTo(Manager::class, 'app_manager_id', 'id');
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
