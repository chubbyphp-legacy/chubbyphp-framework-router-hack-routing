<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting;

use Chubbyphp\Framework\Router\Exceptions\MissingRouteByNameException;
use Chubbyphp\Framework\Router\Exceptions\RouteGenerationException;
use Chubbyphp\Framework\Router\HackRouting\Cache\CacheInterface;
use Chubbyphp\Framework\Router\HackRouting\Cache\NullCache;
use Chubbyphp\Framework\Router\RouteInterface;
use Chubbyphp\Framework\Router\RoutesInterface;
use Chubbyphp\Framework\Router\UrlGeneratorInterface;
use HackRouting\PatternParser\LiteralNode;
use HackRouting\PatternParser\OptionalNode;
use HackRouting\PatternParser\ParameterNode;
use HackRouting\PatternParser\Parser;
use HackRouting\PatternParser\PatternNode;
use Psr\Http\Message\ServerRequestInterface;

final class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var array<string, RouteInterface>
     */
    private array $routesByName;

    private CacheInterface $cache;

    private string $basePath;

    public function __construct(RoutesInterface $routes, ?CacheInterface $cache = null, string $basePath = '')
    {
        $this->routesByName = $routes->getRoutesByName();
        $this->cache = $cache ?? new NullCache();
        $this->basePath = $basePath;
    }

    /**
     * @param array<string, string> $attributes
     * @param array<string, mixed>  $queryParams
     */
    public function generateUrl(
        ServerRequestInterface $request,
        string $name,
        array $attributes = [],
        array $queryParams = []
    ): string {
        $uri = $request->getUri();
        $requestTarget = $this->generatePath($name, $attributes, $queryParams);

        return $uri->getScheme().'://'.$uri->getAuthority().$requestTarget;
    }

    /**
     * @param array<string, string> $attributes
     * @param array<string, mixed>  $queryParams
     */
    public function generatePath(string $name, array $attributes = [], array $queryParams = []): string
    {
        $route = $this->getRoute($name);
        $routePath = $route->getPath();

        $path = $this->pathFromNodes($this->getParsedRouteByName($route), $name, $routePath, $attributes);

        if ([] === $queryParams) {
            return $this->basePath.$path;
        }

        return $this->basePath.$path.'?'.http_build_query($queryParams);
    }

    private function getRoute(string $name): RouteInterface
    {
        if (!isset($this->routesByName[$name])) {
            throw MissingRouteByNameException::create($name);
        }

        return $this->routesByName[$name];
    }

    private function getParsedRouteByName(RouteInterface $route): PatternNode
    {
        return $this->cache->get($route->getName(), static fn () => Parser::parse($route->getPath()));
    }

    /**
     * @param array<string, string> $attributes
     */
    private function pathFromNodes(PatternNode $patternNode, string $name, string $path, array $attributes): string
    {
        $path = '';
        foreach ($patternNode->getChildren() as $childNode) {
            if ($childNode instanceof LiteralNode) {
                $path .= $childNode->getText();
            } elseif ($childNode instanceof ParameterNode) {
                $path .= $this->getAttributeValue($childNode, $name, $path, $attributes);
            } elseif ($childNode instanceof OptionalNode) {
                try {
                    $path .= $this->pathFromNodes($childNode->getPattern(), $name, $path, $attributes);
                } catch (RouteGenerationException $e) {
                    $previous = $e->getPrevious();
                    if (null === $previous || 3 !== $previous->getCode()) {
                        throw $e;
                    }
                }
            }
        }

        return $path;
    }

    /**
     * @param array<string, string> $attributes
     */
    private function getAttributeValue(ParameterNode $parameterNode, string $name, string $path, array $attributes): string
    {
        $attribute = $parameterNode->getName();

        if (!isset($attributes[$attribute])) {
            throw RouteGenerationException::create(
                $name,
                $path,
                $attributes,
                new \RuntimeException(sprintf('Missing attribute "%s"', $attribute), 3)
            );
        }

        $value = (string) $attributes[$attribute];

        $regexp = $parameterNode->getRegexp();

        $pattern = '!^'.$regexp.'$!';

        if (null !== $regexp && 1 !== preg_match($pattern, $value)) {
            throw RouteGenerationException::create(
                $name,
                $path,
                $attributes,
                new \RuntimeException(sprintf(
                    'Not matching value "%s" with pattern "%s" on attribute "%s"',
                    $value,
                    $regexp,
                    $attribute
                ), 4)
            );
        }

        return $value;
    }
}
