<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\RouteParser;

use Chubbyphp\Framework\Router\RouteInterface;
use HackRouting\PatternParser\PatternNode;

interface RouteParserInterface
{
    public function parse(RouteInterface $route): PatternNode;
}
