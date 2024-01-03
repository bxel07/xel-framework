<?php

namespace Setup\bootstrap {

    use PDO;
    use Swoole\Http\Server;
    use Swoole\Coroutine\Channel;
    use Swoole\Coroutine as run;
    use Swoole\Coroutine as go;

    class bootstrap
    {
        private Server $http;
        private Channel $pool;

        /**
         * @return $this
         * Setup database connection pool based on env
         */
        public function setupDatabasePool(): self
        {
            $database = require __DIR__ . "/../config/database.php";
            $this->pool = new Channel($database['mysql']['pool_size']);

            for ($i = 0; $i < $database['mysql']['pool_size']; $i++) {
                run\run(function () use ($database) {
                    $pdo = new PDO(
                        "mysql:host={$database['mysql']['host']};port={$database['mysql']['port']};dbname={$database['mysql']['database']}",
                        $database['mysql']['user'],
                        $database['mysql']['password']
                    );
                    $this->pool->push($pdo);
                });
            }

            return $this;
        }

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
                echo "Swoole http server is started at http://$server->host:$server->port\n";
            });

            // Start listening to requests
            $this->http->on('request', function ($request, $response) {
                // Handle the request using the database connection pool
                $schedule = new go\Scheduler();
                $schedule->add(function () use ($request, $response){
                    $this->handleRequest($request, $response);
                });

               $schedule->start();
            });

            // Start the server
            $this->http->start();
        }

        /**
         * Handle HTTP requests
         */
        private function handleRequest($request, $response): void
        {
            go(function () use ($request, $response) {
                $pdo = $this->pool->pop();

                if (!$pdo) {
                    $response->status(500);
                    $response->end("Internal Server Error: Unable to get a database connection");
                    return;
                }


                // Perform a simple query
                $stmt = $pdo->query('SELECT * FROM users');
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Set the response content type
                $response->header('Content-Type', 'application/json');

                // Send the query result as JSON response
                $response->end(json_encode($result));

                // Release the connection back to the pool
                $this->pool->push($pdo);
            });
            // Get a connection from the pool

        }
    }
}
