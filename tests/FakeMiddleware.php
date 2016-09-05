<?php

namespace Middleland\Tests;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;

class FakeMiddleware implements MiddlewareInterface
{
    private $char;

    public function __construct($char = '.')
    {
        $this->char = $char;
    }

    public function process(RequestInterface $request, DelegateInterface $next)
    {
        $response = $next->next($request);
        $response->getBody()->write($this->char);

        return $response;
    }
}
