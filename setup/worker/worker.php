<?php
return [
    'http_server' => [
        'host' => '127.0.0.1',
        'port' => 9501,
        'mode' => 1,
        'options' => [

            /**
             * If use mode 1 or base mode just comment the options
             */


            /**
             * Setup for mode 2 or process 2
             */

//            'reactor_num' => 2,
//            'worker_num' => 4,
//            'backlog' => 128,
//            'max_request' => 100,
//            'dispatch_mode' => 3,
//            'max_coroutine' => 3000,
//            'open_tcp_keepalive' => true,
//            'tcp_keepidle' => 600,
//            'tcp_keepinterval' => 60,
//            'tcp_keepcount' => 5,
//            'tcp_fastopen' => true,
//            'max_wait_time' => 60,
//            'reload_async' => true,

        /**
         * Setup for ssl secure connection
         */
            'open_http2_protocol' => false,


        ],
    ]
];
