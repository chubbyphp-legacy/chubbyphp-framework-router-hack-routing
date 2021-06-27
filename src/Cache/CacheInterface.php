<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\Cache;

use HackRouting\PatternParser\PatternNode;

interface CacheInterface
{
    public function get(string $item, callable $callback): PatternNode;
}
