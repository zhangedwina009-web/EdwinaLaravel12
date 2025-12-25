<?php

namespace App\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CwaApiClient
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.cwa.url'),
            'timeout'  => 5, // timeout
        ]);
    }

    public function connect(string $datasetId, array $query = [])
    {
        try {
            $response = $this->client->get($datasetId, [
                'query' => array_merge($query, [
                    'Authorization' => config('services.cwa.key'),
                ]),
            ]);
            $content=$response->getBody()->getContents();
            $content=json_decode($content);
            $result=data_get($content,'records.Locations.0.Location.0.WeatherElement','查無資料');
            // logger()->info("connect". print_r($result, true));
            return $result;

        } catch (RequestException $e) {
            throw $e; // 先單純丟出去
        }
    }
}
