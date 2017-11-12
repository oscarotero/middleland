<?php
declare(strict_types = 1);

namespace Middleland\Matchers;

use Psr\Http\Message\ServerRequestInterface;

class Path implements MatcherInterface
{
    private $path;
    private $result = true;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        if ($path[0] === '!') {
            $this->result = false;
            $path = substr($path, 1);
        }

        $this->path = rtrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();

        return (($path === $this->path) || stripos($path, $this->path.'/') === 0) === $this->result;
    }
}
