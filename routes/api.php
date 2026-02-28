<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusStopController;

Route::get('/bus-stops', [BusStopController::class, 'getBusStops']);
Route::get('/bus-stops/{id}', [BusStopController::class, 'show']);
Route::post('/bus-stops', [BusStopController::class, 'store']);
