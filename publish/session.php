<?php

return [
    'name'          => 'YNGPHP_SESSION_ID',
    'handler'       => [
        'class'   => '\Yng\Session\Handlers\Cache',
        'options' => [
            //            'path' => env('storage_path') . 'session',
            'ttl' => 3600,
        ]
    ],
    'cookie_expire' => time() + 3600,
];
