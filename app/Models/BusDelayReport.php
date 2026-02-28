<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusDelayReport extends Model
{
    protected $fillable = [
        'user_id',
        'origin_bus_stop_id',
        'destination_bus_stop_id',
        'delay_minutes',
        'scheduled_arrival_time',
        'arrived_on_time',
        'comment',
    ];

    protected $casts = [
        'arrived_on_time' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function originBusStop()
    {
        return $this->belongsTo(BusStop::class, 'origin_bus_stop_id');
    }

    public function destinationBusStop()
    {
        return $this->belongsTo(BusStop::class, 'destination_bus_stop_id');
    }
}
