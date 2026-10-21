<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Without this file Laravel 11+ answers /api/* for any origin. Nothing
    | needs CORS here: the Vue app is same-origin and the token API is called
    | server-to-server (dora-metrics). Laravel 6 sent no CORS headers either.
    | List paths/origins here if a browser client on another origin is added.
    |
    */

    'paths' => [],

    'allowed_methods' => ['GET'],

    'allowed_origins' => [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Authorization', 'Accept', 'Content-Type'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
