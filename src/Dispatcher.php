<?php

namespace Middleland;

use Closure;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements MiddlewareInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middleware;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @param MiddlewareInterface[] $middleware
     */
    public function __construct(array $middleware, ContainerInterface $container = null)
    {
        if (empty($middleware)) {
            throw new LogicException('Empty middleware queue');
        }

        $this->middleware = $middleware;
        $this->container = $container;
    }

    /**
     * Return the next available middleware frame in the queue.
     *
     * @return MiddlewareInterface|false
     */
    public function next(ServerRequestInterface $request)
    {
        next($this->middleware);

        return $this->get($request);
    }

    /**
     * Return the next available middleware frame in the middleware.
     *
     * @param ServerRequestInterface $request
     *
     * @return MiddlewareInterface|false
     */
    private function get(ServerRequestInterface $request)
    {
        $frame = current($this->middleware);

        if ($frame === false) {
            return $frame;
        }

        if (is_array($frame)) {
            $conditions = $frame;
            $frame = array_pop($conditions);

            foreach ($conditions as $condition) {
                if ($condition === true) {
                    continue;
                }

                if ($condition === false) {
                    return $this->next($request);
                }

                if (is_string($condition)) {
                    $condition = new Matchers\Path($condition);
                } elseif (!is_callable($condition)) {
                    throw new InvalidArgumentException('Invalid matcher. Must be a boolean, string or a callable');
                }

                if (!$condition($request)) {
                    return $this->next($request);
                }
            }
        }

        if (is_string($frame)) {
            if ($this->container === null) {
                throw new InvalidArgumentException(sprintf('No valid middleware provided (%s)', $frame));
            }

            $frame = $this->container->get($frame);
        }

        if ($frame instanceof Closure) {
            return $this->createMiddlewareFromClosure($frame);
        }

        if ($frame instanceof MiddlewareInterface) {
            return $frame;
        }

        throw new InvalidArgumentException(sprintf('No valid middleware provided (%s)', is_object($frame) ? get_class($frame) : gettype($frame)));
    }

    /**
     * Dispatch the request, return a response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        reset($this->middleware);

        return $this->get($request)->process($request, $this->createRequestHandler());
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        reset($this->middleware);

        return $this->get($request)->process($request, $this->createRequestHandler($next));
    }

    /**
     * Create a request handler for the current stack
     *
     * @param RequestHandlerInterface $next
     *
     * @return RequestHandlerInterface
     */
    private function createRequestHandler(RequestHandlerInterface $next = null): RequestHandlerInterface
    {
        return new class($this, $next) implements RequestHandlerInterface {
            private $dispatcher;
            private $next;

            /**
             * @param Dispatcher                   $dispatcher
             * @param RequestHandlerInterface|null $next
             */
            public function __construct(Dispatcher $dispatcher, RequestHandlerInterface $next = null)
            {
                $this->dispatcher = $dispatcher;
                $this->next = $next;
            }

            /**
             * {@inheritdoc}
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $frame = $this->dispatcher->next($request);

                if ($frame === false) {
                    if ($this->next !== null) {
                        return $this->next->handle($request);
                    }

                    throw new LogicException('Middleware queue exhausted');
                }

                return $frame->process($request, $this);
            }
        };
    }

    /**
     * Create a middleware from a closure
     *
     * @param Closure $handler
     *
     * @return MiddlewareInterface
     */
    private function createMiddlewareFromClosure(Closure $handler): MiddlewareInterface
    {
        return new class($handler) implements MiddlewareInterface {
            private $handler;

            /**
             * @param Closure $handler
             */
            public function __construct(Closure $handler)
            {
                $this->handler = $handler;
            }

            /**
             * {@inheritdoc}
             */
            public function process(ServerRequestInterface $request, RequestHandlerInterface $next)
            {
                $response = call_user_func($this->handler, $request, $next);

                if (!($response instanceof ResponseInterface)) {
                    throw new LogicException('The middleware must return a ResponseInterface');
                }

                return $response;
            }
        };
    }
}
