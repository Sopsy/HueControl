<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Repository\SensorRepository;

final class DeleteUnusedMemorySensors
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(...$args): void
    {
        echo "Deleting unused generic sensors...\n";
        (new SensorRepository($this->bridge->api()))->deleteUnusedGeneric();
    }
}