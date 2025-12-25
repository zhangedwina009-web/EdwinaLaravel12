<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;
class WeatherService
{   
    // 宣告一個屬性 $api，型別是 CwaApiClient
    protected CwaApiClient $api;

    // Laravel 的 依賴注入 (Dependency Injection)
    // 當 Controller 用 WeatherService 時，Laravel 自動 new CwaApiClient 並注入
    public function __construct(CwaApiClient $api)
    {
        $this->api = $api;
    }

    // 專門處理快取
    public function process(string $LocationName)
    {   
        /* 
        *$key快取 key 的前綴字+key快取的唯一識別碼
        *ttlTime To Live，快取存活時間（秒）
        *callback
        * 如果 key 已存在 → 直接回傳快取值
        * 如果 key 不存在 → 執行 callback，結果存入快取，然後回傳
        */
        $key= ($LocationName ?? 'all') . '_weather_week';
        $ttl=0;//1小時
         // 用 class method 當 callback
         return Cache::remember($key, $ttl, function() use ($LocationName) {
            return $this->getWeeklyWeather($LocationName);
        });    
    }

    

    // 功能：取得一週天氣資料
    // $city = null：如果沒傳，回傳所有城市
    // : array：告訴 PHP 這個方法一定會回傳陣列（Mid+ 型別安全）

    public function getWeeklyWeather(?string $LocationName)
    {  
       $result= $this->api->connect("F-D0047-091",[
                                    'LocationName' => $LocationName,
                                    'ElementName' => '最高溫度,最低溫度,天氣預報綜合描述',

                                     ]);
                                     logger()->info("getWeeklyWeather");
                                     return  $this->DataWrangling($result);
    }

     private function DataWrangling($data)
    {
        $collect_data=collect($data);
        // 取每日最高溫
        // substr(字串, 起始位置, 長度)

        $MaxTemperature= collect($collect_data->get(0)->Time)
        ->map(function($times)use($collect_data){
            return [
            'Date' =>substr(data_get($times,'StartTime'),0,10),
            'temperature'=>data_get($times,'ElementValue.0.MaxTemperature')
            ];
        })
        ->groupBy('Date')
        ->map(function ($items, $date) {
            return [
                'Date' => $date,
                'MaxTemperature' => $items->max('temperature') // 一天中最低的最低溫
            ];
        })->values();

        // dd($MaxTemperature->all());
        // 取每日最低溫
        $MinTemperature= collect($collect_data->get(1)->Time)
        ->map(function($times)use($collect_data){
            return [
                    'Date' =>substr(data_get($times,'StartTime'),0,10),
                    'temperature'=>data_get($times,'ElementValue.0.MinTemperature')
                    ];
        })
        ->groupBy('Date')
        ->map(function($items,$date){
            return[
                    'Date'=>$date,
                    'MinTemperature' => $items->min('temperature') 
                ];
        })->values();
        // dd($MinTemperature->all());
                
        // 天氣預報綜合描述
        $WeatherDescription= collect($collect_data->get(2)->Time)
        ->map(function($times)use($collect_data){
            $startTime = Carbon::parse(data_get($times,'StartTime'))
            ->setTimezone('Asia/Taipei')
            ->format('Y-m-d H:i');
        return [
                'Date' => substr($startTime,0,10), 
                'Time' =>$startTime,
                'WeatherDescription'=>data_get($times,'ElementValue.0.WeatherDescription')
                ];
                });
            //  dd($WeatherDescription->all());

                // 將高低溫對齊同一個日期
                $chartData = collect($MaxTemperature)->map(function($maxItem) use ($MinTemperature, $WeatherDescription) {
                $minItem = $MinTemperature->firstWhere('Date', $maxItem['Date']);
                $weather = $WeatherDescription->where('Date', $maxItem['Date']);
                return [
                    'Date' => $maxItem['Date'],
                    'MaxTemperature' => $maxItem['MaxTemperature'],
                    'MinTemperature' => $minItem['MinTemperature'] ?? null,
                    'WeatherDescription' => $weather->values()->all() // 保留時段資訊
                ];
        });     
            
        // dd($chartData->all());
        logger()->info("chartData". print_r($chartData, true));

        return $chartData;

    }


    
    
    
        // private → 只能自己呼叫
    // ?string參數可以是「字串」或「null」
    // private function DataWrangling($data)
    // {
    //     $collect_data=collect($data);
    //     // 取每日最高溫
    //     $MaxTemperature= collect($collect_data->get(0)->Time)
    //     ->map(function($times)use($collect_data){
    //         return [
    //         'ElementName'=>data_get($collect_data->get(0),'ElementName'),
    //         'Date' =>data_get($times,'StartTime'),
    //         'temperature'=>data_get($times,'ElementValue.0.MaxTemperature')
    //         ];
    //     });
    //     dd($MaxTemperature->all());
    //     // 取每日最低溫
    //             $MinTemperature= collect($collect_data->get(1)->Time)
    //             ->map(function($times)use($collect_data){
    //                 return [
    //                 'ElementName'=>data_get($collect_data->get(1),'ElementName'),
    //                 'Date' =>data_get($times,'StartTime'),
    //                 'temperature'=>data_get($times,'ElementValue.0.MinTemperature')
    //                 ];
    //             });
    //             // dd($MinTemperature->all());
    //     // 天氣預報綜合描述
    //             $WeatherDescription= collect($collect_data->get(2)->Time)
    //             ->map(function($times)use($collect_data){
    //                 return [
    //                 'ElementName'=>data_get($collect_data->get(2),'ElementName'),
    //                 'Date' =>data_get($times,'StartTime'),
    //                 'temperature'=>data_get($times,'ElementValue.0.WeatherDescription')
    //                 ];
    //             });
    //             // dd($WeatherDescription->all());

        


    // }
}
