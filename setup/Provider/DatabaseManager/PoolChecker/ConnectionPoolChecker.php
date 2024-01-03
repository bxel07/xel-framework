<?php

namespace Setup\Provider\DatabaseManager\PoolChecker;
use Generator;
use PDO;
use Throwable;
use Setup\Provider\DatabaseManager\Exceptions\ErrorHandler;
use Swoole\Coroutine\Channel;

trait ConnectionPoolChecker
{
    private function isHealthy(PDO $connection):bool {
        try {
            $test = $connection->query('SELECT 1');
            return $test !== false;
        } catch (Throwable $e) {
            ErrorHandler::handleError($e);
            return false;
        }
    }

    private function removeConnection(PDO $connection , Channel $pool): void
    {
        function filterConnection(Channel $pool, PDO $connection): Generator
        {
            while (!$pool->isEmpty()) {
                $currentConnection = $pool->pop();
                if ($currentConnection !== $connection) {
                    yield $currentConnection;
                }
            }
        }

        $newPool = new Channel($pool->capacity - 1);
        foreach (filterConnection($pool, $connection) as $current) {
            $newPool->push($current);
        }

        // Update the original pool
        while (!$newPool->isEmpty()) {
            $pool->push($newPool->pop());
        }
    }
}