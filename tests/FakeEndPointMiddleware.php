<?php

namespace Middleland\Tests;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Zend\Diactoros\Response;
use Psr\Http\Message\RequestInterface;

class FakeEndPointMiddleware implements MiddlewareInterface
{
    public function process(RequestInterface $request, DelegateInterface $next)
    {
        return new Response();
    }
}
