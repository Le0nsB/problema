<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusStop extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
    ];

    public function delayReports()
    {
        return $this->hasMany(BusDelayReport::class, 'origin_bus_stop_id');
    }
}
