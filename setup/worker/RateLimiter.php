<?php

namespace Setup\worker;

use Swoole\Atomic;
use Swoole\Http\Response;

class RateLimiter
{

    public Response $response;
    public Atomic $requestCounter;

    public int $limit;
    public function __construct($limit, $counter)
    {
        $this->limit = $limit;
        $this->requestCounter = $counter;
    }

    public function incrementCounter(): void
    {
        $this->requestCounter->add();
    }

    public function decrementCounter(): void
    {
        $this->requestCounter->sub();
    }

    public function atomicLimit(): void
    {
        if ($this->incrementCounter() > $this->limit) {
           $this->Response();
        }
    }

    private function Response(): void
    {
        $this->response->status(429);
        $this->response->end('Too Many Request');
    }


}