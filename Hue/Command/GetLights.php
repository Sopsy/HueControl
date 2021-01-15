<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\LightRepository;

final class GetLights implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        echo "Lights in {$this->bridge->name()}:\n\n";

        foreach ((new LightRepository($this->bridge->api()))->all() AS $light) {
            echo "{$light->id()}: {$light->name()}\n";
            echo "  - Manufacturer: {$light->manufacturer()}\n";
            echo "  - Product: {$light->productName()}\n";
            echo "  - Type: {$light->type()}\n";
            echo "  - Model: {$light->model()}\n";
            echo "  - Color gamut: {$light->gamutType()}\n";
        }
    }
}