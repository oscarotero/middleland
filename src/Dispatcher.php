<?php

namespace Middleland;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use InvalidArgumentException;
use LogicException;
use Closure;

class Dispatcher implements MiddlewareInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middleware;

    /**
     * @param MiddlewareInterface[] $middleware
     */
    public function __construct(array $middleware)
    {
        if (empty($middleware)) {
            throw new LogicException('Empty middleware queue');
        }

        $this->middleware = $middleware;
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

                if (!($condition instanceof Matchers\MatcherInterface)) {
                    $condition = new Matchers\Path($condition);
                }

                if (!$condition->match($request)) {
                    return $this->next($request);
                }
            }
        }

        if ($frame === false) {
            return $frame;
        }

        if ($frame instanceof Closure) {
            return $this->createMiddleware($frame);
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

        return $this->get($request)->process($request, $this->createDelegate());
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        reset($this->middleware);
        
        return $this->get($request)->process($request, $this->createDelegate($delegate));
    }

    /**
     * Create a delegate for the current stack
     *
     * @param DelegateInterface $delegate
     *
     * @return DelegateInterface
     */
    private function createDelegate(DelegateInterface $delegate = null): DelegateInterface
    {
        return new class($this, $delegate) implements DelegateInterface {
            private $dispatcher;
            private $delegate;

            /**
             * @param Dispatcher $dispatcher
             * @param DelegateInterface|null $delegate
             */
            public function __construct(Dispatcher $dispatcher, DelegateInterface $delegate = null)
            {
                $this->dispatcher = $dispatcher;
                $this->delegate = $delegate;
            }

            /**
             * {@inheritdoc}
             */
            public function process(ServerRequestInterface $request)
            {
                $frame = $this->dispatcher->next($request);

                if ($frame === false) {
                    if ($this->delegate !== null) {
                        return $this->delegate->process($request);
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
    private function createMiddleware(Closure $handler): MiddlewareInterface
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
            public function process(ServerRequestInterface $request, DelegateInterface $delegate)
            {
                $response = call_user_func($this->handler, $request, $delegate);

                if (!($response instanceof ResponseInterface)) {
                    throw new LogicException('The middleware must return a ResponseInterface');
                }

                return $response;
            }
        };
    }
}
