<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\RouteParser;

use Chubbyphp\Framework\Router\RouteInterface;
use HackRouting\PatternParser\PatternNode;

final class MemoryRouteParser implements RouteParserInterface
{
    private RouteParserInterface $routeParser;

    /**
     * @var array<string, PatternNode>
     */
    private $cache = [];

    public function __construct(?RouteParserInterface $routeParser = null)
    {
        $this->routeParser = $routeParser ?? new RouteParser();
    }

    public function parse(RouteInterface $route): PatternNode
    {
        $name = $route->getName();

        return $this->cache[$name] ?? $this->cache[$name] = $this->routeParser->parse($route);
    }
}
