<?php

namespace Middleland;

use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Dispatcher implements DelegateInterface
{
    protected $queue;
    protected $next;

    /**
     * @param array $queue The middleware queue
     */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Execute the next middleware frame.
     *
     * @see DelegateInterface
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function next(RequestInterface $request)
    {
        $frame = next($this->queue);

        if ($frame === false && $this->next !== null) {
            return $this->next->next($request);
        }

        return $frame->process($request, $this);
    }
}
