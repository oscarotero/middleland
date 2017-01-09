<?php

namespace Middleland\Tests;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class FakeMiddleware implements MiddlewareInterface
{
    private $char;

    public function __construct($char = '.')
    {
        $this->char = $char;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $next)
    {
        $response = $next->process($request);
        $response->getBody()->write($this->char);

        return $response;
    }
}
