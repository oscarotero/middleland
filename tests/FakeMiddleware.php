<?php
declare(strict_types = 1);

namespace Middleland\Tests;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FakeMiddleware implements MiddlewareInterface
{
    private $char;

    public function __construct($char = '.')
    {
        $this->char = strval($char);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($request);
        $response->getBody()->write($this->char);

        return $response;
    }
}
