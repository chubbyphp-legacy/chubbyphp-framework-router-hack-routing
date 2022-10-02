<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Framework\Router\HackRouting\Unit;

use Chubbyphp\Framework\Router\HackRouting\RouteMatcher;
use Chubbyphp\Framework\Router\Route;
use Chubbyphp\Framework\Router\RoutesByName;
use Chubbyphp\HttpException\HttpException;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use HackRouting\Cache\MemoryCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Chubbyphp\Framework\Router\HackRouting\RouteMatcher
 *
 * @internal
 */
final class RouteMatcherTest extends TestCase
{
    use MockByCallsTrait;

    public const UUID_PATTERN = '[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}';

    public function testMatchFound(): void
    {
        /** @var MockObject|RequestHandlerInterface $requestHandler1 */
        $requestHandler1 = $this->getMockByCalls(RequestHandlerInterface::class);

        $route1 = Route::post('/api/pets', 'pet_create', $requestHandler1);

        /** @var MockObject|RequestHandlerInterface $requestHandler2 */
        $requestHandler2 = $this->getMockByCalls(RequestHandlerInterface::class);

        $route2 = Route::get('/api/pets', 'pet_list', $requestHandler2);

        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class, [
            Call::create('getPath')->with()->willReturn('/api/pets'),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getMethod')->with()->willReturn('GET'),
            Call::create('getUri')->with()->willReturn($uri),
        ]);

        $cache = new MemoryCache();

        $serialzedCache = serialize($cache);

        $routeMatcher = new RouteMatcher(new RoutesByName([$route1, $route2]), $cache);

        self::assertSame($route2->getName(), $routeMatcher->match($request)->getName());

        self::assertNotSame($serialzedCache, serialize($cache));
    }

    public function testMatchNotFound(): void
    {
        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class, [
            Call::create('getPath')->with()->willReturn('/'),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getMethod')->with()->willReturn('GET'),
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getRequestTarget')->with()->willReturn('/'),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/api/pets', 'pet_list', $requestHandler);

        $routeMatcher = new RouteMatcher(new RoutesByName([$route]));

        try {
            $routeMatcher->match($request);
            self::fail('Excepted exception');
        } catch (HttpException $e) {
            self::assertSame('Not Found', $e->getTitle());
            self::assertSame(404, $e->getStatus());
            self::assertSame([
                'type' => 'https://datatracker.ietf.org/doc/html/rfc2616#section-10.4.5',
                'status' => 404,
                'title' => 'Not Found',
                'detail' => 'The page "/" you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly.',
                'instance' => null,
            ], $e->jsonSerialize());
        }
    }

    public function testMatchMethodNotAllowed(): void
    {
        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class, [
            Call::create('getPath')->with()->willReturn('/api/pets'),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getMethod')->with()->willReturn('POST'),
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getMethod')->with()->willReturn('POST'),
            Call::create('getRequestTarget')->with()->willReturn('/api/pets?offset=1&limit=20'),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/api/pets', 'pet_list', $requestHandler);

        $routeMatcher = new RouteMatcher(new RoutesByName([$route]));

        try {
            $routeMatcher->match($request);
            self::fail('Excepted exception');
        } catch (HttpException $e) {
            self::assertSame('Method Not Allowed', $e->getTitle());
            self::assertSame(405, $e->getStatus());
            self::assertSame([
                'type' => 'https://datatracker.ietf.org/doc/html/rfc2616#section-10.4.6',
                'status' => 405,
                'title' => 'Method Not Allowed',
                'detail' => 'Method "POST" at path "/api/pets?offset=1&limit=20" is not allowed. Must be one of: "GET"',
                'instance' => null,
            ], $e->jsonSerialize());
        }
    }

    public function testMatchWithTokensNotMatch(): void
    {
        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class, [
            Call::create('getPath')->with()->willReturn('/api/pets/1'),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getMethod')->with()->willReturn('GET'),
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getRequestTarget')->with()->willReturn('/api/pets/1'),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/api/pets/{id:'.self::UUID_PATTERN.'}', 'pet_read', $requestHandler);

        $routeMatcher = new RouteMatcher(new RoutesByName([$route]));

        try {
            $routeMatcher->match($request);
            self::fail('Excepted exception');
        } catch (HttpException $e) {
            self::assertSame('Not Found', $e->getTitle());
            self::assertSame(404, $e->getStatus());
            self::assertSame([
                'type' => 'https://datatracker.ietf.org/doc/html/rfc2616#section-10.4.5',
                'status' => 404,
                'title' => 'Not Found',
                'detail' => 'The page "/api/pets/1" you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly.',
                'instance' => null,
            ], $e->jsonSerialize());
        }
    }

    public function testMatchWithTokensMatch(): void
    {
        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class, [
            Call::create('getPath')->with()->willReturn('/api/pets/8b72750c-5306-416c-bba7-5b41f1c44791'),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getMethod')->with()->willReturn('GET'),
            Call::create('getUri')->with()->willReturn($uri),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/api/pets/{id:'.self::UUID_PATTERN.'}', 'pet_read', $requestHandler);

        $routeMatcher = new RouteMatcher(new RoutesByName([$route]));

        self::assertSame($route->getName(), $routeMatcher->match($request)->getName());
    }
}
