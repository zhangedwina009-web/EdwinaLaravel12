<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'weather/*'], // 你要允許的路徑
    'allowed_methods' => ['*'], // 允許的 HTTP 方法
    'allowed_origins' => [
        'http://localhost:9000',      // 前端開發用
        'http://127.0.0.1:9000',      // 前端開發用
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,

];
