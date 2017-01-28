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
        $this->path = rtrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function match(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();

        return ($path === $this->path) || stripos($path, $this->path.'/') === 0;
    }
}
