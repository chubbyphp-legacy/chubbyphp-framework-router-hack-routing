<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Framework\Router\HackRouting\Unit;

use Chubbyphp\Framework\Router\Exceptions\MissingRouteByNameException;
use Chubbyphp\Framework\Router\Exceptions\RouteGenerationException;
use Chubbyphp\Framework\Router\HackRouting\Cache\MemoryCache;
use Chubbyphp\Framework\Router\HackRouting\UrlGenerator;
use Chubbyphp\Framework\Router\Route;
use Chubbyphp\Framework\Router\Routes;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Chubbyphp\Framework\Router\HackRouting\UrlGenerator
 *
 * @internal
 */
final class UrlGeneratorTest extends TestCase
{
    use MockByCallsTrait;

    public function testGenerateUri(): void
    {
        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class, [
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getUri')->with()->willReturn($uri),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/user/{id:\d+}[/{name}]/view', 'user', $requestHandler);

        $cache = new MemoryCache();

        $serializedCache = serialize($cache);

        $router = new UrlGenerator(new Routes([$route]), $cache);

        self::assertSame(
            'https://user:password@localhost/user/1/view',
            $router->generateUrl($request, 'user', ['id' => 1])
        );
        self::assertSame(
            'https://user:password@localhost/user/1/view?key=value',
            $router->generateUrl($request, 'user', ['id' => 1], ['key' => 'value'])
        );
        self::assertSame(
            'https://user:password@localhost/user/1/test/view',
            $router->generateUrl($request, 'user', ['id' => 1, 'name' => 'test'])
        );
        self::assertSame(
            'https://user:password@localhost/user/1/test/view?key1=value1&key2=value2',
            $router->generateUrl(
                $request,
                'user',
                ['id' => 1, 'name' => 'test'],
                ['key1' => 'value1', 'key2' => 'value2']
            )
        );

        self::assertNotSame($serializedCache, serialize($cache));
    }

    public function testGenerateUriWithMissingAttribute(): void
    {
        $this->expectException(RouteGenerationException::class);
        $this->expectExceptionMessage('Route generation for route "user" with path "/user/" with attributes "{}" failed. Missing attribute "id"');
        $this->expectExceptionCode(3);

        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getUri')->with()->willReturn($uri),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/user/{id:\d+}[/{name}]', 'user', $requestHandler);

        $router = new UrlGenerator(new Routes([$route]));
        $router->generateUrl($request, 'user');
    }

    public function testGenerateUriWithNotMatchingAttribute(): void
    {
        $this->expectException(RouteGenerationException::class);
        $this->expectExceptionMessage(
            'Route generation for route "user" with path "/user/" with attributes "{"id":"a3bce0ca-2b7c-4fc6-8dad-ecdcc6907791"}" failed. Not matching value "a3bce0ca-2b7c-4fc6-8dad-ecdcc6907791" with pattern "\d+" on attribute "id"'
        );
        $this->expectExceptionCode(3);

        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getUri')->with()->willReturn($uri),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/user/{id:\d+}[/{name}]', 'user', $requestHandler);

        $router = new UrlGenerator(new Routes([$route]));
        $router->generateUrl($request, 'user', ['id' => 'a3bce0ca-2b7c-4fc6-8dad-ecdcc6907791']);
    }

    public function testGenerateUriWithBasePath(): void
    {
        /** @var MockObject|UriInterface $uri */
        $uri = $this->getMockByCalls(UriInterface::class, [
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
            Call::create('getScheme')->with()->willReturn('https'),
            Call::create('getAuthority')->with()->willReturn('user:password@localhost'),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getUri')->with()->willReturn($uri),
            Call::create('getUri')->with()->willReturn($uri),
        ]);

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/user/{id:\d+}[/{name}]', 'user', $requestHandler);

        $router = new UrlGenerator(new Routes([$route]), null, '/path/to/directory');

        self::assertSame(
            'https://user:password@localhost/path/to/directory/user/1',
            $router->generateUrl($request, 'user', ['id' => 1])
        );
        self::assertSame(
            'https://user:password@localhost/path/to/directory/user/1?key=value',
            $router->generateUrl($request, 'user', ['id' => 1], ['key' => 'value'])
        );
        self::assertSame(
            'https://user:password@localhost/path/to/directory/user/1/sample',
            $router->generateUrl($request, 'user', ['id' => 1, 'name' => 'sample'])
        );
        self::assertSame(
            'https://user:password@localhost/path/to/directory/user/1/sample?key1=value1&key2=value2',
            $router->generateUrl(
                $request,
                'user',
                ['id' => 1, 'name' => 'sample'],
                ['key1' => 'value1', 'key2' => 'value2']
            )
        );
    }

    public function testGeneratePathWithMissingRoute(): void
    {
        $this->expectException(MissingRouteByNameException::class);
        $this->expectExceptionMessage('Missing route: "user"');

        $router = new UrlGenerator(new Routes([]));
        $router->generatePath('user', ['id' => 1]);
    }

    public function testGeneratePath(): void
    {
        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/user/{id:\d+}[/{name}]', 'user', $requestHandler);

        $router = new UrlGenerator(new Routes([$route]));

        self::assertSame('/user/1', $router->generatePath('user', ['id' => 1]));
        self::assertSame('/user/1?key=value', $router->generatePath('user', ['id' => 1], ['key' => 'value']));
        self::assertSame('/user/1/sample', $router->generatePath('user', ['id' => 1, 'name' => 'sample']));
        self::assertSame(
            '/user/1/sample?key1=value1&key2=value2',
            $router->generatePath(
                'user',
                ['id' => 1, 'name' => 'sample'],
                ['key1' => 'value1', 'key2' => 'value2']
            )
        );
    }

    public function testGeneratePathWithMissingAttribute(): void
    {
        $this->expectException(RouteGenerationException::class);
        $this->expectExceptionMessage('Route generation for route "user" with path "/user/" with attributes "{}" failed. Missing attribute "id"');

        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/user/{id:\d+}[/{name}]', 'user', $requestHandler);

        $router = new UrlGenerator(new Routes([$route]));
        $router->generatePath('user');
    }

    public function testGeneratePathWithBasePath(): void
    {
        /** @var MockObject|RequestHandlerInterface $requestHandler */
        $requestHandler = $this->getMockByCalls(RequestHandlerInterface::class);

        $route = Route::get('/user/{id:\d+}[/{name}]', 'user', $requestHandler);

        $router = new UrlGenerator(new Routes([$route]), null, '/path/to/directory');

        self::assertSame('/path/to/directory/user/1', $router->generatePath('user', ['id' => 1]));
        self::assertSame(
            '/path/to/directory/user/1?key=value',
            $router->generatePath('user', ['id' => 1], ['key' => 'value'])
        );
        self::assertSame(
            '/path/to/directory/user/1/sample',
            $router->generatePath('user', ['id' => 1, 'name' => 'sample'])
        );
        self::assertSame(
            '/path/to/directory/user/1/sample?key1=value1&key2=value2',
            $router->generatePath(
                'user',
                ['id' => 1, 'name' => 'sample'],
                ['key1' => 'value1', 'key2' => 'value2']
            )
        );
    }
}
