<?php

namespace Setup\Provider\DatabaseManager;

use Generator;
use PDO;
use PDOException;
use RuntimeException;
use Swoole\Coroutine;
use Throwable;
use Setup\Provider\DatabaseManager\Exceptions\ErrorHandler;
use Setup\Provider\DatabaseManager\PoolChecker\ConnectionPoolChecker;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine as run;

class DatabaseManager
{
    private const DSN = 'mysql:host=localhost;dbname=laravel;charset=utf8mb4';
    private const USERNAME = 'root';
    private const PASSWORD = 'Todokana1ko!';
    private const SLEEP_TIME = 1.5;
    private const POOL_THRESHOLD = 1;

    private Channel $connectionPool;
    private int $maxConnections = 3;
    private int $threshold = self::POOL_THRESHOLD;

    use ConnectionPoolChecker;

    public function __construct()
    {
        $this->connectionPool = new Channel($this->maxConnections);
        run\go(function () {
            $this->initializeConnections();
        });
    }

    private function initializeConnections(): void
    {
        for ($i = 0; $i < $this->maxConnections; $i++) {
            run\go(function () {
                try {
                    $db = $this->createPdoConnection();
                    $this->connectionPool->push($db);
                } catch (Throwable $e) {
                    $this->handleError($e);
                }
            });
        }
    }

    private function createPdoConnection(): PDO
    {
        return new PDO(
            self::DSN,
            self::USERNAME,
            self::PASSWORD,
            [PDO::ATTR_PERSISTENT => true]
        );
    }

    private function generateConnections(): Generator
    {
        $attempts = 0;
        while ($attempts < $this->maxConnections) {
            try {
                $connection = $this->connectionPool->pop($this->getChannelPopTimeout());
                if ($this->isHealthy($connection)) {
                    yield $connection;
                    return;
                }
                $this->removeConnection($connection, $this->connectionPool);
            } catch (\Swoole\Error\ChannelTimeout $timeoutException) {
                $this->handleError($timeoutException);
            } catch (Throwable $e) {
                $this->handleError($e);
            }

            $attempts++;
            Coroutine::sleep(self::SLEEP_TIME);
        }
    }

    private function getChannelPopTimeout(): float
    {
        return 1.0; // Set an appropriate timeout for pop operation, e.g., 1 second
    }

    public function getConnection(): PDO|string|null
    {
        try {
            foreach ($this->generateConnections() as $connection) {
                return $connection;
            }

            if ($this->connectionPool->length() < $this->threshold) {
                $this->createConnection();
                return $this->getConnection();
            }
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }

        return null;
    }

    private function createConnection(): void
    {
        run\go(function () {
            try {
                $db = $this->createPdoConnection();
                $this->connectionPool->push($db);
            } catch (Throwable $e) {
                $this->handleError($e);
            }
        });
    }

    public function releaseConnection(PDO $connection): void
    {
        $attempts = 0;
        while ($attempts < $this->maxConnections) {
            try {
                $this->connectionPool->push($connection, $this->getChannelPushTimeout());
                return;
            } catch (\Swoole\Error\ChannelTimeout $timeoutException) {
                $this->handleError($timeoutException);
            } catch (Throwable $e) {
                $this->handleError($e);
            }

            $attempts++;
            Coroutine::sleep(self::SLEEP_TIME);
        }
    }

    private function getChannelPushTimeout(): float
    {
        return 1.0; // Set an appropriate timeout for push operation, e.g., 1 second
    }

    private function handleError(Throwable $e): void
    {
        ErrorHandler::handleError($e);
    }
}
