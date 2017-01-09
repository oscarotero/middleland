<?php

namespace Middleland\Tests;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class FakeEndPointMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $next)
    {
        return new Response();
    }
}
