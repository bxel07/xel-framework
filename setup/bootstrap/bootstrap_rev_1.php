<?php

namespace Setup\bootstrap;

use Setup\Provider\DatabaseManager\Builder\XgenQuery_1;
use Setup\Provider\DatabaseManager\DatabaseManager;
use Setup\Provider\DatabaseManager\Query;
use Setup\worker\RateLimiter;
use Swoole\Http\Server;
use Swoole\Coroutine as Co;
use Swoole\Atomic;

class bootstrap_rev_1
{
    private Server $http;
    private RateLimiter $requestCounter;

    /**
     * @return $this
     * Setup HTTP server
     */
    public function setupServer(): self
    {
        $worker = require __DIR__ . '/../worker/worker.php';
        $this->http = new Server($worker['http_server']['host'], $worker['http_server']['port'], $worker['http_server']['mode']);
        $this->http->set($worker['http_server']['options']);
        $this->requestCounter = new RateLimiter(60,new Atomic());

        return $this;
    }

    /**
     * @return void
     * Start the server
     */
    public function run(): void
    {
        $this->http->on('start', function ($server) {
            echo "Swoole http server is started at $server->host:$server->port\n";
        });

        // Start listening to requests
        $this->http->on('request', function ($request, $response){
            Co::create(function () use ($request, $response) {
                $this->requestCounter->incrementCounter();
                $this->requestCounter->atomicLimit();

                // Your existing code
                $dbHandler = new DatabaseManager();
                $xgen = new XgenQuery_1($dbHandler);
                $controller = new Query($request, $response, $request->server['path_info'], $request->server['request_method'], $xgen);
                $controller->handleRequest();

                // Decrement the request counter after processing the request
                $this->requestCounter->decrementCounter();
            });
        });

        // Start the server
        $this->http->start();
    }
}
