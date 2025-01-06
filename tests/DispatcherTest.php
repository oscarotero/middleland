<?php
declare(strict_types = 1);

namespace Middleland\Tests;

use Datetime;
use InvalidArgumentException;
use LogicException;
use Middleland\Dispatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversClass Dispatcher
 */
#[CoversClass(Dispatcher::class)]
class DispatcherTest extends TestCase
{
    public function testEndPointMiddleware(): void
    {
        $dispatcher = new Dispatcher([
            new FakeEndPointMiddleware(),
        ]);

        $this->assertResponse('', $dispatcher(new ServerRequest()));
    }

    public function testMiddleware(): void
    {
        $dispatcher = new Dispatcher([
            new FakeMiddleware(1),
            new FakeMiddleware(2),
            new FakeMiddleware(3),
            new FakeEndPointMiddleware(),
        ]);

        $this->assertResponse('321', $dispatcher->dispatch(new ServerRequest()));
    }

    public function testClosure(): void
    {
        $dispatcher = new Dispatcher([
            function ($request, $next) {
                $response = $next->handle($request);
                $response->getBody()->write('hello');
                return $response;
            },
            new FakeEndPointMiddleware(),
        ]);

        $this->assertResponse('hello', $dispatcher->dispatch(new ServerRequest()));
    }

    public function testInnerMiddleware(): void
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

        $this->assertResponse('87654321', $dispatcher->dispatch(new ServerRequest()));
        //Reuse
        $this->assertResponse('87654321', $dispatcher->dispatch(new ServerRequest()));
    }

    public function testMatchers(): void
    {
        $dispatcher = new Dispatcher([
            new FakeMiddleware(1),
            ['/world', false, new FakeMiddleware(2)],
            ['/world', new FakeMiddleware(3)],
            [new FakeMiddleware(4)],
            new FakeEndPointMiddleware(),
        ]);

        $this->assertResponse('431', $dispatcher->dispatch(new ServerRequest([], [], '/world')));
    }

    public function testContainer(): void
    {
        $dispatcher = new Dispatcher([
            '1',
            '2',
            [false, '3'],
            [true, '4'],
            new FakeEndPointMiddleware(),
        ], new FakeContainer());

        $this->assertResponse('421', $dispatcher->dispatch(new ServerRequest()));
    }

    public function testDispatcherReuse(): void
    {
        $dispatcher1 = new Dispatcher([
            new FakeMiddleware(1),
            new FakeMiddleware(2),
            new FakeMiddleware(3),
            new FakeEndPointMiddleware(),
        ]);

        $dispatcher2 = new Dispatcher([
            new FakeMiddleware(4),
            new FakeMiddleware(5),
            new FakeMiddleware(6),
            $dispatcher1,
        ]);

        $this->assertResponse('321', $dispatcher1->dispatch(new ServerRequest()));
        $this->assertResponse('321654', $dispatcher2->dispatch(new ServerRequest()));
        $this->assertResponse('321', $dispatcher1->dispatch(new ServerRequest()));
    }

    public function testEmptyDispatcherException(): void
    {
        $this->expectException(LogicException::class);

        $dispatcher = new Dispatcher([]);
    }

    public function testExhaustedException(): void
    {
        $this->expectException(LogicException::class);

        $dispatcher = new Dispatcher([
            new FakeMiddleware(),
            new FakeMiddleware(),
        ]);

        $dispatcher->dispatch(new ServerRequest());
    }

    public function testInvalidMiddlewareException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $dispatcher = new Dispatcher([
            new Datetime(),
        ]);

        $dispatcher->dispatch(new ServerRequest());
    }

    public function testInvalidStringMiddlewareException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $dispatcher = new Dispatcher([
            'invalid',
        ]);

        $dispatcher->dispatch(new ServerRequest());
    }

    public function testInvalidMatcherException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $dispatcher = new Dispatcher([
            [new Datetime(), new FakeMiddleware()],
        ]);

        $dispatcher->dispatch(new ServerRequest());
    }

    private function assertResponse(string $body, ResponseInterface $response)
    {
        $this->assertEquals($body, (string) $response->getBody());
    }
}
