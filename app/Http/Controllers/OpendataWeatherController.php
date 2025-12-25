<?php

namespace App\Http\Controllers;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpendataWeatherController extends Controller
{
    // Controller
    public function weekly(Request $request, WeatherService $weather)
    {
        $locationName = $request->query('LocationName');
        logger()->info( "OpendataWeatherController.ph".$locationName );
        $data = $weather->process($locationName);

        return response()->json($data);
    }
}
