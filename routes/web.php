<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpendataWeatherController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/weather/week', [OpendataWeatherController::class, 'weekly']);