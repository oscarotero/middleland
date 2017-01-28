<?php

namespace Middleland\Tests;

use Middleland\Dispatcher;
use Middleland\Matchers\Path;
use Zend\Diactoros\ServerRequest;

class PathMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function pathProvider()
    {
        return [
            ['/hello', '/hello', true],
            ['/hello', '/hello/', true],
            ['/hello', '/helloworld', false],
            ['/hello', '/hello/world', true],
            ['/hello', '/world', false],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testMatcher(string $pattern, string $path, bool $valid)
    {
        $matcher = new Path($pattern);
        $request = new ServerRequest([], [], $path);

        $this->assertSame($valid, $matcher->match($request));
    }
}
