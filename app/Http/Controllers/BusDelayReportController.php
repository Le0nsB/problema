<?php

namespace App\Http\Controllers;

use App\Models\BusDelayReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusDelayReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin_bus_stop_id' => 'required|exists:bus_stops,id',
            'destination_bus_stop_id' => 'required|exists:bus_stops,id',
            'delay_minutes' => 'required|integer|min:-60|max:180',
            'scheduled_arrival_time' => 'required|date_format:H:i',
            'arrived_on_time' => 'nullable|boolean',
            'comment' => 'nullable|string|max:500',
        ]);

        $report = BusDelayReport::create([
            'user_id' => Auth::id(),
            'origin_bus_stop_id' => $validated['origin_bus_stop_id'],
            'destination_bus_stop_id' => $validated['destination_bus_stop_id'],
            'delay_minutes' => $validated['delay_minutes'],
            'scheduled_arrival_time' => $validated['scheduled_arrival_time'],
            'arrived_on_time' => $validated['arrived_on_time'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        $report->load('user', 'originBusStop', 'destinationBusStop');

        return response()->json([
            'success' => true,
            'message' => 'Maršruta kavējuma ziņojums veiksmīgi iesniegts!',
            'report' => $report,
        ]);
    }
}
