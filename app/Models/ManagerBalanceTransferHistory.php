<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerBalanceTransferHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function receiver()
    {
        return $this->hasOne(Manager::class, 'id', 'reciver_id');
    }
    public function sender()
    {
        return $this->hasOne(Manager::class, 'id', 'sender_id');
    }
}
