<?php
return [
    'http_server' => [
        'host' => 'http://localhost',
        'port' => 9501,
        'mode' => 1,
        'options' => [

            /**
             * If you use mode 1 or base mode just comment the Advance options
             * this recommended when dealing with I/O Bound operation
             */

            'worker_num' => 8,
            'max_connection' => 1024,
            'enable_coroutine' => true,
            'max_coroutine' => 5000,

            /**
             * Setup for mode 2 or process 2
             * this recommended when dealing with CPU Bound operation
             */

              'dispatch_mode' => 1,
              'reload_async' => true,

        /**
         * Setup for ssl secure connection
         */
            'open_http2_protocol' => false,
        ],
    ]
];
