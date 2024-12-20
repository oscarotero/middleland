<?php
declare(strict_types = 1);

namespace Middleland\Tests;

use Middleland\Matchers\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversClass Pattern
 */
#[CoversClass(Pattern::class)]
class PatternMatcherTest extends TestCase
{
    public static function pathProvider(): array
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
            ['*.png', '/hello/world.png', true],
            ['!*.png', '/hello/world.jpg', true],
            ['!*.jpg', '/hello/world.jpg', false],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    #[DataProvider('pathProvider')]
    public function testMatcher(string $pattern, string $path, bool $valid, int $flags = 0): void
    {
        $matcher = new Pattern($pattern, $flags);
        $request = new ServerRequest([], [], $path);

        $this->assertSame($valid, $matcher($request));
    }
}
