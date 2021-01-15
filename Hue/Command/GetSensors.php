<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\SensorRepository;

final class GetSensors implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        echo "Sensors in {$this->bridge->name()}:\n\n";

        foreach ((new SensorRepository($this->bridge->api()))->all() AS $sensor) {
            echo "{$sensor->id()}: {$sensor->name()} ({$sensor->type()}: {$sensor->model()})\n";
        }
    }
}