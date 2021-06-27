<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Framework\Router\HackRouting\Unit\Cache;

use Chubbyphp\Framework\Router\HackRouting\Cache\FileCache;
use Chubbyphp\Mock\MockByCallsTrait;
use HackRouting\PatternParser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Framework\Router\HackRouting\Cache\FileCache
 *
 * @internal
 */
final class FileCacheTest extends TestCase
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

        $directory = sys_get_temp_dir().'/file-cache-test-'.uniqid().uniqid();

        mkdir($directory);

        $cache = new FileCache($directory);

        self::assertSame($parsedRoute, $cache->get('name', $callback));

        $cache->get('name', $callback);
        $cache->get('name', $callback);

        self::assertSame(1, $parseCount);

        foreach (glob($directory.'/*') as $file) {
            unlink($file);
        }

        rmdir($directory);
    }
}
