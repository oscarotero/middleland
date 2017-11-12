<?php
declare(strict_types = 1);

namespace Middleland;

use Closure;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements MiddlewareInterface, RequestHandlerInterface
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
     * @var RequestHandlerInterface|null
     */
    private $next;

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
     * Return the current available middleware frame in the middleware.
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

            if (!self::executeConditions($request, $conditions)) {
                next($this->middleware);
                return $this->get($request);
            }
        }

        if (is_string($frame)) {
            if ($this->container === null) {
                throw new InvalidArgumentException(sprintf('No valid middleware provided (%s)', $frame));
            }

            $frame = $this->container->get($frame);
        }

        if ($frame instanceof Closure) {
            return self::createMiddlewareFromClosure($frame);
        }

        if ($frame instanceof MiddlewareInterface) {
            return $frame;
        }

        throw new InvalidArgumentException(sprintf('No valid middleware provided (%s)', is_object($frame) ? get_class($frame) : gettype($frame)));
    }

    /**
     * Dispatch the request, return a response.
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        reset($this->middleware);

        return $this->get($request)->process($request, $this);
    }

    /**
     * @see RequestHandlerInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        next($this->middleware);
        $frame = $this->get($request);

        if ($frame === false) {
            if ($this->next !== null) {
                return $this->next->handle($request);
            }

            throw new LogicException('Middleware queue exhausted');
        }

        return $frame->process($request, $this);
    }

    /**
     * @see MiddlewareInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $this->next = $next;

        return $this->dispatch($request);
    }

    /**
     * Create a middleware from a closure
     */
    private static function createMiddlewareFromClosure(Closure $handler): MiddlewareInterface
    {
        return new class($handler) implements MiddlewareInterface {
            private $handler;

            public function __construct(Closure $handler)
            {
                $this->handler = $handler;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
            {
                return call_user_func($this->handler, $request, $next);
            }
        };
    }

    /**
     * Evaluate conditions
     */
    private static function executeConditions(ServerRequestInterface $request, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if ($condition === true) {
                continue;
            }

            if ($condition === false) {
                return false;
            }

            if (is_string($condition)) {
                $condition = new Matchers\Path($condition);
            } elseif (!is_callable($condition)) {
                throw new InvalidArgumentException('Invalid matcher. Must be a boolean, string or a callable');
            }

            if ($condition($request)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
