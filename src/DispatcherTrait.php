<?php

namespace Middleland;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Interop\Http\Middleware\DelegateInterface;

trait DispatcherTrait
{
    protected $queue;
    protected $next;

    /**
     * @param array $queue The middleware queue
     */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request): ResponseInterface
    {
        reset($this->queue);
        $frame = current($this->queue);

        return $frame->process($request, $this);
    }

    /**
     * Magic method to use the dispatcher as callable.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request): ResponseInterface
    {
        return $this->next($request);
    }

    /**
     * Execute the next middleware frame.
     *
     * @see DelegateInterface
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function next(RequestInterface $request): ResponseInterface
    {
        $frame = next($this->queue);

        if ($frame === false && $this->next !== null) {
            return $this->next->next($request);
        }

        return $frame->process($request, $this);
    }

    /**
     * Execute the dispatcher like a middleware.
     *
     * @see MiddlewareInterface
     *
     * @param RequestInterface  $request
     * @param DelegateInterface $next
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, DelegateInterface $next): ResponseInterface
    {
        $this->next = $next;

        return $this->dispatch($request);
    }
}
