<?php

namespace Middleland\Matchers;

use Psr\Http\Message\ServerRequestInterface;

class Pattern implements MatcherInterface
{
    private $pattern;
    private $flags;

    /**
     * @param string $path
     * @param int    $flags
     */
    public function __construct(string $pattern, $flags = 0)
    {
        $this->pattern = $pattern;
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function match(ServerRequestInterface $request): bool
    {
        return fnmatch($this->pattern, $request->getUri()->getPath(), $this->flags);
    }
}
