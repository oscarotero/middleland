<?php
declare(strict_types = 1);

namespace Middleland\Tests;

use Middleland\Matchers\Path;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversClass Path
 */
#[CoversClass(Path::class)]
class PathMatcherTest extends TestCase
{
    public static function pathProvider(): array
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
    #[DataProvider('pathProvider')]
    public function testMatcher(string $pattern, string $path, bool $valid): void
    {
        $matcher = new Path($pattern);
        $request = new ServerRequest([], [], $path);

        $this->assertSame($valid, $matcher($request));
    }
}
