<?php

namespace Middleland\Tests;

use Middleland\ClientDispatcher;
use Middleland\ServerDispatcher;
use Zend\Diactoros\Request;
use Zend\Diactoros\ServerRequest;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testEndPointMiddleware()
    {
        $dispatcher = new ClientDispatcher([
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new Request());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
    }

    public function testMiddleware()
    {
        $dispatcher = new ClientDispatcher([
            new FakeMiddleware(1),
            new FakeMiddleware(2),
            new FakeMiddleware(3),
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new Request());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('321', (string) $response->getBody());
    }

    public function testInnerClientMiddleware()
    {
        $dispatcher = new ClientDispatcher([
            new FakeMiddleware(1),
            new ClientDispatcher([
                new FakeMiddleware(2),
                new FakeMiddleware(3),
                new FakeMiddleware(4),
                new ClientDispatcher([
                    new FakeMiddleware(5),
                    new FakeMiddleware(6),
                    new FakeMiddleware(7),
                ]),
            ]),
            new FakeMiddleware(8),
            new FakeEndPointMiddleware(),
        ]);

        $response = $dispatcher->dispatch(new Request());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('87654321', (string) $response->getBody());

        //Reuse
        $response = $dispatcher->dispatch(new Request());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('87654321', (string) $response->getBody());
    }

    public function testInnerServerMiddleware()
    {
        $dispatcher = new ServerDispatcher([
            new FakeMiddleware(1),
            new ServerDispatcher([
                new FakeMiddleware(2),
                new FakeMiddleware(3),
                new FakeMiddleware(4),
                new ServerDispatcher([
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
}
