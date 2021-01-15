<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\SensorRepository;

final class DeleteUnusedMemorySensors implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        echo "Deleting unused generic sensors...\n";
        (new SensorRepository($this->bridge->api()))->deleteUnusedGeneric();
    }
}