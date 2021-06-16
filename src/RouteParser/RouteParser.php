<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\RouteParser;

use Chubbyphp\Framework\Router\RouteInterface;
use HackRouting\PatternParser\Parser;
use HackRouting\PatternParser\PatternNode;

final class RouteParser implements RouteParserInterface
{
    public function parse(RouteInterface $route): PatternNode
    {
        return Parser::parse($route->getPath());
    }
}
