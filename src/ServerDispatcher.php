<?php

namespace Middleland;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Interop\Http\Middleware\{ServerMiddlewareInterface, DelegateInterface};

class ServerDispatcher implements ServerMiddlewareInterface, DelegateInterface
{
    use DispatcherTrait {
        DispatcherTrait::process as private processRequest;
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
    public function process(ServerRequestInterface $request, DelegateInterface $next): ResponseInterface
    {
        return $this->processRequest($request, $next);
    }
}
