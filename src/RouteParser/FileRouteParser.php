<?php

declare(strict_types=1);

namespace Chubbyphp\Framework\Router\HackRouting\RouteParser;

use Chubbyphp\Framework\Router\RouteInterface;
use HackRouting\PatternParser\PatternNode;

final class FileRouteParser implements RouteParserInterface
{
    private RouteParserInterface $routeParser;

    private string $directory;

    public function __construct(?RouteParserInterface $routeParser = null, ?string $directory = null)
    {
        $this->routeParser = $routeParser ?? new RouteParser();
        $this->directory = $directory ?? sys_get_temp_dir().'/chubbyphp-hack-routing';
    }

    public function parse(RouteInterface $route): PatternNode
    {
        $filename = $this->directory.'/'.md5($route->getName()).'/parsed-route.php';
        if (!file_exists($filename)) {
            file_put_contents($filename, "<?php return unserialize('".serialize($this->routeParser->parse($route))."');");
        }

        return require $filename;
    }
}
