<?php
declare(strict_types = 1);

namespace Middleland\Tests;

use Middleland\Matchers\Path;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;

class PathMatcherTest extends TestCase
{
    public function pathProvider()
    {
        return [
            ['/hello', '/hello', true],
            ['/hello', '/hello/', true],
            ['/hello', '/helloworld', false],
            ['/hello', '/hello/world', true],
            ['/hello', '/world', false],
            ['!/hello', '/world', true],
            ['!/hello', '/hello', false],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testMatcher(string $pattern, string $path, bool $valid)
    {
        $matcher = new Path($pattern);
        $request = new ServerRequest([], [], $path);

        $this->assertSame($valid, $matcher($request));
    }
}
