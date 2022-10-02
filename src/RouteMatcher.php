<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting;

use Chubbyphp\Framework\Router\RouteInterface;
use Chubbyphp\Framework\Router\RouteMatcherInterface;
use Chubbyphp\Framework\Router\RoutesByNameInterface;
use Chubbyphp\HttpException\HttpException;
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
    public function __construct(RoutesByNameInterface $routes, ?CacheInterface $cache = null)
    {
        $this->routesByName = $routes->getRoutesByName();

        $router = new Router($cache ?? new NullCache());

        foreach ($this->routesByName as $route) {
            $router->addRoute($route->getMethod(), $route->getPath(), $route->getName());
        }

        $this->router = $router;
    }

    public function match(ServerRequestInterface $request): RouteInterface
    {
        $method = $request->getMethod();
        $path = rawurldecode($request->getUri()->getPath());

        try {
            [$name, $attributes] = $this->router->match($method, $path);

            /** @var RouteInterface $route */
            $route = $this->routesByName[$name];

            return $route->withAttributes($attributes);
        } catch (HackMethodNotAllowedException $e) {
            throw HttpException::createMethodNotAllowed([
                'detail' => sprintf(
                    'Method "%s" at path "%s" is not allowed. Must be one of: "%s"',
                    $request->getMethod(),
                    $request->getRequestTarget(),
                    implode('", "', $e->getAllowedMethods()),
                ),
            ]);
        } catch (HackNotFoundException) {
            throw HttpException::createNotFound([
                'detail' => sprintf(
                    'The page "%s" you are looking for could not be found.'
                    .' Check the address bar to ensure your URL is spelled correctly.',
                    $request->getRequestTarget()
                ),
            ]);
        }
    }
}
