<?php
declare(strict_types = 1);

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
    public function __invoke(ServerRequestInterface $request): bool;
}
