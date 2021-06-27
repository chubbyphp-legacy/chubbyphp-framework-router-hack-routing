<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\Cache;

use HackRouting\PatternParser\PatternNode;

final class ApcuCache implements CacheInterface
{
    public function get(string $item, callable $callback): PatternNode
    {
        $success = false;

        $result = apcu_fetch($item, $success);

        if ($success && false !== $result) {
            return $result;
        }

        $result = $callback();

        apcu_add($item, $result);

        return $result;
    }
}
