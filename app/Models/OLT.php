<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OLT extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function sub_zone()
    {
        return $this->belongsTo(SubZone::class, 'sub_zone_id');
    }
    public function o_l_t_pon_port_status()
    {
        return $this->belongsTo(OLTPonPortStatus::class);
    }
}
