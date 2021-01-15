<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\SensorRepository;
use function number_format;

final class GetTemp implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        echo "Sensors in {$this->bridge->name()}:\n\n";

        foreach ((new SensorRepository($this->bridge->api()))->all(SensorRepository::TYPE_TEMP) AS $sensor) {
            echo "{$sensor->id()}: {$sensor->name()} ({$sensor->type()}: {$sensor->model()})\n";
            echo '  - Temp: ' . number_format($sensor->temp(), 1, '.', ' ') . "°C\n";
        }
    }
}