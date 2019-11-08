<?php
declare(strict_types=1);

namespace Hue;

use function array_key_exists;
use function array_slice;

final class Application
{
    private $routes;
    private $bridge;

    public function __construct(string ...$args)
    {
        $this->routes = require __DIR__ . '/Config/Routes.php';
        $this->bridge = new Bridge($args[1], $args[2]);

        if (!isset($args[3])) {
            $command = false;
        } else {
            $command = $args[3];
        }

        $args = array_slice($args, 4);

        $this->dispatch($command, ...$args);
    }

    private function dispatch(string $command, string ...$args): void
    {
        if (!array_key_exists($command, $this->routes)) {
            echo 'Usage: php cli.php <bridge ip> <username> <command>';

            return;
        }

        ($this->routes[$command])()->handle(...$args);
    }
}