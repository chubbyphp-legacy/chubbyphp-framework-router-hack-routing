<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\RouteParser;

use Chubbyphp\Framework\Router\RouteInterface;
use HackRouting\PatternParser\PatternNode;

final class ApcuRouteParserDecorator implements RouteParserInterface
{
    private RouteParserInterface $routeParser;

    public function __construct(?RouteParserInterface $routeParser = null)
    {
        $this->routeParser = $routeParser ?? new RouteParser();
    }

    public function parse(RouteInterface $route): PatternNode
    {
        $key = self::class.':'.$route->getName();

        if (!apcu_exists($key)) {
            apcu_add($key, $this->routeParser->parse($route));
        }

        return apcu_fetch($key);
    }
}
