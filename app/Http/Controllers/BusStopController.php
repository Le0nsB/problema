<?php

namespace App\Http\Controllers;

use App\Models\BusDelayReport;
use App\Models\BusStop;
use Illuminate\Http\Request;

class BusStopController extends Controller
{
    public function index()
    {
        $busStops = BusStop::all();
        return view('map', compact('busStops'));
    }

    public function getBusStops()
    {
        $busStops = BusStop::with(['delayReports' => function($query) {
            $query->with('user')->latest()->limit(5);
        }])->get();

        return response()->json($busStops);
    }

    public function show($id)
    {
        $busStop = BusStop::with(['delayReports' => function($query) {
            $query->with(['user', 'originBusStop', 'destinationBusStop'])->latest()->limit(10);
        }])->findOrFail($id);

        $toStopReportsQuery = BusDelayReport::where('destination_bus_stop_id', $busStop->id);
        $toReportsCount = (clone $toStopReportsQuery)->count();
        $averageDelayToStop = (clone $toStopReportsQuery)->avg('delay_minutes');

        $busStop->setAttribute('to_reports_count', $toReportsCount);
        $busStop->setAttribute(
            'average_delay_to_stop',
            $averageDelayToStop !== null ? round((float) $averageDelayToStop, 1) : null
        );

        return response()->json($busStop);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $existingStop = BusStop::where(function($query) use ($validated) {
            $query->whereBetween('latitude', [$validated['latitude'] - 0.001, $validated['latitude'] + 0.001])
                  ->whereBetween('longitude', [$validated['longitude'] - 0.001, $validated['longitude'] + 0.001]);
        })->first();

        if ($existingStop) {
            return response()->json($existingStop);
        }

        $busStop = BusStop::create($validated);

        return response()->json($busStop, 201);
    }
}
