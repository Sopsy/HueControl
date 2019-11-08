<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Repository\LightRepository;

final class GetLights
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(...$args): void
    {
        echo "Lights in {$this->bridge->name()}:\n\n";

        foreach ((new LightRepository($this->bridge->api()))->getAll()->all() AS $light) {
            echo "{$light->id()}: {$light->name()}\n";
            echo "  - Manufacturer: {$light->manufacturer()}\n";
            echo "  - Product: {$light->productName()}\n";
            echo "  - Type: {$light->type()}\n";
            echo "  - Model: {$light->model()}\n";
            echo "  - Color gamut: {$light->gamutType()}\n";
        }
    }
}