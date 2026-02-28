<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusStopController;
use App\Http\Controllers\BusDelayReportController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;


Route::get('/', [BusStopController::class, 'index'])->name('map');

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::post('/reports', [BusDelayReportController::class, 'store'])->middleware('auth')->name('reports.store');

