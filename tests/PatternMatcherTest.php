<?php

namespace Middleland\Tests;

use Middleland\Dispatcher;
use Middleland\Matchers\Pattern;
use Zend\Diactoros\ServerRequest;

class PatternMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function pathProvider()
    {
        return [
            ['/hello', '/hello', true],
            ['/hello', '/hello/', false],
            ['/hello', '/hello/world', false],
            ['/hello/*', '/hello/world', true],
            ['/hello/*', '/hello/under/world', true],
            ['/hello/*', '/hello/under/world', false, FNM_PATHNAME],
            ['/hello/*/*', '/hello/under/world', true, FNM_PATHNAME],
            ['/hello/*.png', '/hello/world', false],
            ['/hello/*.png', '/hello/world.png', true],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testMatcher(string $pattern, string $path, bool $valid, int $flags = 0)
    {
        $matcher = new Pattern($pattern, $flags);
        $request = new ServerRequest([], [], $path);

        $this->assertSame($valid, $matcher->match($request));
    }
}
