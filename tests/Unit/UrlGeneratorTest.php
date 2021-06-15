<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Framework\Router\HackRouting\Unit;

use Chubbyphp\Framework\RequestHandler\CallbackRequestHandler;
use Chubbyphp\Framework\Router\HackRouting\UrlGenerator;
use Chubbyphp\Framework\Router\Route;
use Chubbyphp\Framework\Router\Routes;
use Chubbyphp\Mock\MockByCallsTrait;
use HackRouting\Cache\MemoryCache;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Framework\Router\HackRouting\UrlGenerator
 *
 * @internal
 */
final class UrlGeneratorTest extends TestCase
{
    use MockByCallsTrait;

    public function testGeneratePath(): void
    {
        $urlGenerator = new UrlGenerator(new Routes([
            Route::get('/hello/{name:[a-z]+}-suffix[/optional-part[.{format:json|xml}]]', 'hello', new CallbackRequestHandler(
                static function (): void {
                }
            )),
        ]), new MemoryCache(), '/prefix');

        self::assertSame(
            '/prefix/hello/world-suffix/optional-part?key=value',
            $urlGenerator->generatePath('hello', ['name' => 'world'], ['key' => 'value'])
        );

        self::assertSame(
            '/prefix/hello/world-suffix/optional-part.xml?key=value',
            $urlGenerator->generatePath('hello', ['name' => 'world', 'format' => 'xml'], ['key' => 'value'])
        );
    }
}
