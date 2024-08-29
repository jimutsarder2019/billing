<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded = [];
    function ticket_category()
    {
        return $this->belongsTo(TicketCategory::class);
    }
    function customer()
    {
        return $this->belongsTo(TicketCategory::class);
    }
    function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
