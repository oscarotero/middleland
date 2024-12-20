<?php
declare(strict_types = 1);

namespace Middleland\Tests;

use Middleland\Matchers\Accept;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversClass Accept
 */
#[CoversClass(Accept::class)]
class AcceptMatcherTest extends TestCase
{
    public static function pathProvider(): array
    {
        return [
            ['text/html', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', true],
            ['!text/html', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', false],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    #[DataProvider('pathProvider')]
    public function testMatcher(string $pattern, string $accept, bool $valid): void
    {
        $matcher = new Accept($pattern);
        $request = (new ServerRequest())->withHeader('Accept', $accept);

        $this->assertSame($valid, $matcher($request));
    }
}
