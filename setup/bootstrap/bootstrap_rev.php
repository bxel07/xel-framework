<?php

    namespace Setup\bootstrap;
    use Setup\Provider\DatabaseManager\Builder\XgenQuery;
    use Setup\Provider\DatabaseManager\DatabaseManager;
    use Setup\Provider\DatabaseManager\Query;
    use Swoole\Http\Server;
    use Swoole\Coroutine as Co;


    class bootstrap_rev
    {
        private Server $http;
      

        /**
         * @return $this
         * Setup HTTP server
         */
        public function setupServer(): self
        {
            $worker = require __DIR__ . '/../worker/worker.php';
            $this->http = new Server($worker['http_server']['host'], $worker['http_server']['port'], $worker['http_server']['mode']);
            $this->http->set($worker['http_server']['options']);

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
            $this->http->on('request', function ($request, $response) {
                Co\go(function () use ($request, $response) {

                    $dbHandler = new DatabaseManager();
                    $dbConnection = $dbHandler->getConnection();

                    $xgen = new XgenQuery($dbHandler);
                    $controller = new Query($request, $response, $request->server['path_info'], $request->server['request_method'], $xgen);

                    $controller->handleRequest();

                    // Release the connection back to the pool
                    $dbHandler->releaseConnection($dbConnection);
                });
            });

            // Start the server
            $this->http->start();
        }

    }

