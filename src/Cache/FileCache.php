<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\Cache;

use HackRouting\PatternParser\PatternNode;

final class FileCache implements CacheInterface
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?? sys_get_temp_dir();
    }

    public function get(string $item, callable $callback): PatternNode
    {
        $file = $this->directory.'/'.md5($item).'-pattern-node.php';

        if (is_file($file)) {
            return require $file;
        }

        $result = $callback();

        file_put_contents($file, "<?php return unserialize('".serialize($result)."');");

        return $result;
    }
}
