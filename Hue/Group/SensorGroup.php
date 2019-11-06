<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Resource\Sensor;
use RuntimeException;

final class SensorGroup
{
    private $items;

    public function __construct(Sensor ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return Sensor[]
     */
    public function sensors(): array
    {
        return $this->items;
    }

    public function sensorByName(string $name): Sensor
    {
        foreach ($this->items AS $item) {
            if ($item->name() === $name) {
                return $item;
            }
        }

        throw new RuntimeException("Sensor '{$name}' not found.");
    }
}