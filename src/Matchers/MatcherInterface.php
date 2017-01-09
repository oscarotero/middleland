<?php

namespace Middleland\Matchers;

use Psr\Http\Message\ServerRequestInterface;

interface MatcherInterface
{
    /**
     * Evaluate if the request matches with the condition
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function match(ServerRequestInterface $request): bool;
}
