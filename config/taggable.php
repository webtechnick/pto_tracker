<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tag Model
    |--------------------------------------------------------------------------
    |
    | This option controls the default session "driver" that will be used on
    | requests. By default, we will use the lightweight native driver but
    | you may specify any of the other wonderful drivers provided here.
    |
    | Supported: "file", "cookie", "database", "apc",
    |            "memcached", "redis", "array"
    |
    */

    'model' => env('TAG_MODEL', 'App\Tag'),

];
