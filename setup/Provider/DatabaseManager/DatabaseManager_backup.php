<?php

namespace Setup\Provider\DatabaseManager;

use Generator;
use PDO;
use RuntimeException;
use Throwable;
use Setup\Provider\DatabaseManager\Exceptions\ErrorHandler;
use Setup\Provider\DatabaseManager\PoolChecker\ConnectionPoolChecker;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine as run;

class DatabaseManager_backup
{
    private Channel $connectionPool;
    private int $maxConnections = 10;
    
    use ConnectionPoolChecker;
    public function __construct()
    {
        $this->connectionPool = new Channel($this->maxConnections);
            $this->initializeConnections();
    }

    private function initializeConnections(): void
    {
            for ($i = 0; $i < $this->maxConnections; $i++) {
                run\go(function (){
                    $dsn = 'mysql:host=localhost;dbname=laravel;charset=utf8mb4';
                    $username = 'root';
                    $password = 'Todokana1ko!';

                    try {
                        $db = new PDO(
                            $dsn,
                            $username,
                            $password,
                            [PDO::ATTR_PERSISTENT => true]
                        );

                        // ? Put the connection into the pool
                        $this->connectionPool->push($db,'1.0');
                    }catch (Throwable $e){
                        ErrorHandler::handleError($e);
                    }
                });

            }
    }

    private function generateConnections():Generator{
        $attempts = 0;
        while ($attempts < $this->maxConnections){
            try {
                $connection = $this->connectionPool->pop();
                if ($this->isHealthy($connection)){
                    yield $connection;
                    return;
                }
                $this->removeConnection($connection, $this->connectionPool);
            }catch (Throwable $e){
                ErrorHandler::handleError($e);
            }

            $attempts++;
            usleep(100000);
        }
    }

    public function getConnection():PDO|string|null
    {
        try {
            foreach ($this->generateConnections() as $connection){
                return $connection;
            }
        }catch (RuntimeException $e){
            return $e->getMessage();
        }
        return null;
    }

    public function releaseConnection(PDO $connection): void
    {
        // ? Release the connection back to the pool
        $this->connectionPool->push($connection);
    }
}
