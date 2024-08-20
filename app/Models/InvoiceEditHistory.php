<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceEditHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
