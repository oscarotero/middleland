<?php

namespace Middleland\Tests;

use Middleland\Dispatcher;
use Middleland\Matchers;
use Zend\Diactoros\ServerRequest;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testEndPointMiddleware()
    {
        $dispatcher = new Dispatcher([
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
    }

    public function testMiddleware()
    {
        $dispatcher = new Dispatcher([
            new FakeMiddleware(1),
            new FakeMiddleware(2),
            new FakeMiddleware(3),
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('321', (string) $response->getBody());
    }

    public function testClosure()
    {
        $dispatcher = new Dispatcher([
            function ($request, $next) {
                $response = $next->process($request);
                $response->getBody()->write('hello');
                return $response;
            },
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('hello', (string) $response->getBody());
    }

    public function testInnerMiddleware()
    {
        $dispatcher = new Dispatcher([
            new FakeMiddleware(1),
            new Dispatcher([
                new FakeMiddleware(2),
                new FakeMiddleware(3),
                new FakeMiddleware(4),
                new Dispatcher([
                    new FakeMiddleware(5),
                    new FakeMiddleware(6),
                    new FakeMiddleware(7),
                ]),
            ]),
            new FakeMiddleware(8),
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('87654321', (string) $response->getBody());

        //Reuse
        $response = $dispatcher->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('87654321', (string) $response->getBody());
    }

    public function testMatchers()
    {
        $dispatcher = new Dispatcher([
            new FakeMiddleware(1),
            ['/world', false, new FakeMiddleware(2)],
            ['/world', new FakeMiddleware(3)],
            [new FakeMiddleware(4)],
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new ServerRequest([], [], '/world'));

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('431', (string) $response->getBody());
    }
}
