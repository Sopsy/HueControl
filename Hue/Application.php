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
        if (!isset($args[1])) {
            echo "Missing Bridge IP\n";
            $this->printUsageHelp();

            return;
        }
        if (!isset($args[2])) {
            echo "Missing username\n";
            $this->printUsageHelp();

            return;
        }
        if (!isset($args[3])) {
            echo "Missing command\n";
            $this->printUsageHelp();

            return;
        }

        $this->bridge = new Bridge($args[1], $args[2]);
        $command = $args[3];

        $args = array_slice($args, 4);

        $this->dispatch($command, ...$args);
    }

    private function dispatch(string $command, string ...$args): void
    {
        if (!class_exists('\Hue\RequestHandler\\' . $command)) {
            echo "Unknown command '{$command}'";

            return;
        }

        /** @var $class RequestHandlerInterface */
        $className = '\Hue\RequestHandler\\' . $command;
        $class = new $className($this->bridge);
        $class->handle(...$args);
    }

    private function printUsageHelp(): void
    {
        echo 'Usage: php cli.php <bridge ip> <username> <command>';
    }
}