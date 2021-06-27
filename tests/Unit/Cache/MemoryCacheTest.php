<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Framework\Router\HackRouting\Unit\Cache;

use Chubbyphp\Framework\Router\HackRouting\Cache\MemoryCache;
use Chubbyphp\Mock\MockByCallsTrait;
use HackRouting\PatternParser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Framework\Router\HackRouting\Cache\MemoryCache
 *
 * @internal
 */
final class MemoryCacheTest extends TestCase
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

        $cache = new MemoryCache();

        self::assertSame($parsedRoute, $cache->get('name', $callback));

        $cache->get('name', $callback);
        $cache->get('name', $callback);

        self::assertSame(1, $parseCount);
    }
}
