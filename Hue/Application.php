<?php
declare(strict_types=1);

namespace Hue;

use Hue\Contract\RequestHandlerInterface;
use function class_exists;

final class Application
{
    private $bridge;

    public function __construct(string $bridgeIp, string $username, string $command = '', string ...$args)
    {
        if (empty($command)) {
            echo "Missing command\n";
            $this->printUsageHelp();

            return;
        }

        if ($command !== 'CreateConfig') {
            if (!empty($username)) {
                $this->bridge = new Bridge($bridgeIp, $username);
            } else {
                echo "Bridge username missing, run CreateConfig command to create one\n";

                return;
            }
        } else {
            $this->bridge = $bridgeIp;
        }

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