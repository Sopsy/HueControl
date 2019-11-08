<?php
declare(strict_types=1);

namespace Hue;

use Hue\Contract\RequestHandlerInterface;
use function array_slice;
use function class_exists;

final class Application
{
    private $bridge;

    public function __construct(string ...$args)
    {
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
        if (!class_exists('\Hue\RequestHandler\\' . $command)) {
            echo 'Usage: php cli.php <bridge ip> <username> <command>';

            return;
        }

        /** @var $class RequestHandlerInterface */
        $className = '\Hue\RequestHandler\\' . $command;
        $class = new $className($this->bridge);
        $class->handle(...$args);
    }
}