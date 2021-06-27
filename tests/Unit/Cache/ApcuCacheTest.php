<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Framework\Router\HackRouting\Unit\Cache;

use Chubbyphp\Framework\Router\HackRouting\Cache\ApcuCache;
use Chubbyphp\Mock\MockByCallsTrait;
use HackRouting\PatternParser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Framework\Router\HackRouting\Cache\ApcuCache
 *
 * @internal
 */
final class ApcuCacheTest extends TestCase
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

        $cache = new ApcuCache();

        self::assertSame($parsedRoute, $cache->get('name', $callback));

        $cache->get('name', $callback);
        $cache->get('name', $callback);

        self::assertSame(1, $parseCount);
    }
}
