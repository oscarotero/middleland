<?php

namespace Middleland;

use Interop\Http\Middleware\MiddlewareInterface;
use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientDispatcher extends Dispatcher implements MiddlewareInterface
{
    /**
     * Dispatch the response after execute the queue.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        reset($this->queue);
        $frame = current($this->queue);

        return $frame->process($request, $this);
    }

    /**
     * Execute the dispatcher like a middleware.
     *
     * @see MiddlewareInterface
     *
     * @param RequestInterface  $request
     * @param DelegateInterface $next
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, DelegateInterface $next)
    {
        $this->next = $next;

        return $this->dispatch($request);
    }
}
