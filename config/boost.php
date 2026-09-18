<?php

return [
    'enabled' => env(
        'BOOST_ENABLED',
        in_array(env('APP_ENV', 'production'), ['local', 'testing'], true),
    ),
    'browser_logs_watcher' => env('BOOST_BROWSER_LOGS_WATCHER', false),
];
