<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\Cache;

use HackRouting\PatternParser\PatternNode;

final class MemoryCache implements CacheInterface
{
    /**
     * @var array<string, PatternNode>
     */
    private array $cache = [];

    public function get(string $item, callable $callback): PatternNode
    {
        return $this->cache[$item] ?? $this->cache[$item] = $callback();
    }
}
