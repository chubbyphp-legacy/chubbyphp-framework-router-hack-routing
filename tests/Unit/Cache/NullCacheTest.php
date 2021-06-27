<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Framework\Router\HackRouting\Unit\Cache;

use Chubbyphp\Framework\Router\HackRouting\Cache\NullCache;
use Chubbyphp\Mock\MockByCallsTrait;
use HackRouting\PatternParser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Framework\Router\HackRouting\Cache\NullCache
 *
 * @internal
 */
final class NullCacheTest extends TestCase
{
    use MockByCallsTrait;

    public function testGet(): void
    {
        $parseCount = 0;
        $parsedRoute = Parser::parse('/hello/{name:[a-z]+}');

        $callback = static function () use (&$parseCount, $parsedRoute) {
            ++$parseCount;

            return $parsedRoute;
        };

        $cache = new NullCache();

        self::assertSame($parsedRoute, $cache->get('name', $callback));

        $cache->get('name', $callback);
        $cache->get('name', $callback);

        self::assertSame(3, $parseCount);
    }
}
