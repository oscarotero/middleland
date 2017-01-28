<?php

namespace Middleland\Tests;

use Middleland\Dispatcher;
use Middleland\Matchers\Accept;
use Zend\Diactoros\ServerRequest;

class AcceptMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function pathProvider()
    {
        return [
            ['text/html', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', true],
            ['!text/html', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', false],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testMatcher(string $pattern, string $accept, bool $valid)
    {
        $matcher = new Accept($pattern);
        $request = (new ServerRequest())->withHeader('Accept', $accept);

        $this->assertSame($valid, $matcher->match($request));
    }
}
