<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting;

use Chubbyphp\Framework\Router\Exceptions\MethodNotAllowedException;
use Chubbyphp\Framework\Router\Exceptions\NotFoundException;
use Chubbyphp\Framework\Router\RouteInterface;
use Chubbyphp\Framework\Router\RouteMatcherInterface;
use Chubbyphp\Framework\Router\RoutesInterface;
use HackRouting\Cache\CacheInterface;
use HackRouting\Cache\NullCache;
use HackRouting\HttpException\MethodNotAllowedException as HackMethodNotAllowedException;
use HackRouting\HttpException\NotFoundException as HackNotFoundException;
use HackRouting\Router;
use Psr\Http\Message\ServerRequestInterface;

final class RouteMatcher implements RouteMatcherInterface
{
    /**
     * @var array<string, RouteInterface>
     */
    private array $routesByName;

    /**
     * @var Router<string>
     */
    private Router $router;

    /**
     * @param CacheInterface<string> $cache
     */
    public function __construct(RoutesInterface $routes, ?CacheInterface $cache = null)
    {
        $this->routesByName = $routes->getRoutesByName();
        $this->router = $this->getRouter($routes, $cache ?? new NullCache());
    }

    public function match(ServerRequestInterface $request): RouteInterface
    {
        $method = $request->getMethod();
        $path = \rawurldecode($request->getUri()->getPath());

        try {
            [$name, $attributes] = $this->router->match($method, $path);

            /** @var RouteInterface $route */
            $route = $this->routesByName[$name];

            return $route->withAttributes($attributes);
        } catch (HackMethodNotAllowedException $e) {
            throw MethodNotAllowedException::create(
                $request->getRequestTarget(),
                $method,
                $e->getAllowedMethods()
            );
        } catch (HackNotFoundException) {
            throw NotFoundException::create($request->getRequestTarget());
        }
    }

    /**
     * @param CacheInterface<string> $cache
     *
     * @return Router<string>
     */
    private function getRouter(RoutesInterface $routes, CacheInterface $cache): Router
    {
        $router = new Router($cache);

        foreach ($routes->getRoutesByName() as $route) {
            $router->addRoute($route->getMethod(), $route->getPath(), $route->getName());
        }

        return $router;
    }
}
