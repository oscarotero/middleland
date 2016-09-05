<?php

namespace Middleland;

use Interop\Http\Middleware\ServerMiddlewareInterface;
use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerDispatcher extends Dispatcher implements ServerMiddlewareInterface
{
    /**
     * Dispatch the response after execute the queue.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        reset($this->queue);
        $frame = current($this->queue);

        return $frame->process($request, $this);
    }

    /**
     * Execute the dispatcher like a middleware.
     *
     * @see ServerMiddlewareInterface
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $next
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $next)
    {
        $this->next = $next;

        return $this->dispatch($request);
    }
}
