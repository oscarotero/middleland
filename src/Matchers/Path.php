<?php

namespace Middleland\Matchers;

use Psr\Http\Message\ServerRequestInterface;

class Path implements MatcherInterface
{
    private $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function match(ServerRequestInterface $request): bool
    {
        return stripos($request->getUri()->getPath(), $this->path) === 0;
    }
}
