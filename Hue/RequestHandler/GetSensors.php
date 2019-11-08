<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Repository\SensorRepository;

final class GetSensors
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(...$args): void
    {
        echo "Sensors in {$this->bridge->name()}:\n\n";

        foreach ((new SensorRepository($this->bridge->api()))->getAll()->all() AS $sensor) {
            echo "{$sensor->id()}: {$sensor->name()} ({$sensor->type()}: {$sensor->modelId()})\n";
        }
    }
}