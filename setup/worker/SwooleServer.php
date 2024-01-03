<?php

namespace Setup\worker;
use Setup\bootstrap\bootstrap;
use Setup\bootstrap\bootstrap_rev_1;
use Swoole\Http\Server;

class SwooleServer
{
    private static ?Server $instance = null;
    private static bootstrap_rev_1 $bootstrapInstance;


    /**
     * @return Server|null
     */
    public static function getInstance(): ?Server
    {
        $worker = require __DIR__.'/../worker/worker.php';
        if (self::$instance === null){
            self::$instance = new Server($worker['http_server']['host'],$worker['http_server']['port'], $worker['http_server']['mode']);
            self::$instance->set($worker['http_server']['options']);
        }

        return self::$instance;
    }


    public static function startServer(): void
    {
        $server = self::getInstance();

        $server->on('start', function ($server) {
            echo "Swoole http server is started at $server->host:$server->port\n";
        });

        // Additional event handlers

        try {
            $server->start();
        } catch (\Throwable $e) {
            // Log the exception and stack trace
            var_dump("Exception: " . $e->getMessage() . "\n");
            var_dump("Stack Trace: \n" . $e->getTraceAsString() . "\n");
        }
    }

    /**
     * Handle HTTP requests
     *
     * @param $request
     * @param $response
     */
    public static function handleRequest($request, $response): void
    {
        // Access Bootstrap instance and create controller
        self::$bootstrapInstance = self::$bootstrapInstance ?? new Bootstrap();
        $controller = self::$bootstrapInstance->createController($request, $response);
        $controller->handleRequest();
    }

}