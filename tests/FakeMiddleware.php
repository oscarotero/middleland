<?php
declare(strict_types = 1);

namespace Middleland\Tests;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FakeMiddleware implements MiddlewareInterface
{
    private $char;

    public function __construct($char = '.')
    {
        $this->char = $char;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($request);
        $response->getBody()->write($this->char);

        return $response;
    }
}
