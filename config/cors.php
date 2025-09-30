<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie','login','logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://192.168.8.11:8000','http://192.168.8.11:9000','http://192.168.50.98:8000','http://192.168.50.98:9000', 'http://10.0.1.23:89','http://10.0.1.23:90'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
