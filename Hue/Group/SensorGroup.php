<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Contract\GroupInterface;
use Hue\Contract\ResourceInterface;
use Hue\Resource\Sensor;
use RuntimeException;

final class SensorGroup implements GroupInterface
{
    private $items;

    public function __construct(Sensor ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return Sensor[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function byName($name): ResourceInterface
    {
        foreach ($this->items AS $item) {
            if ($item->name() === $name) {
                return $item;
            }
        }

        throw new RuntimeException("Sensor '{$name}' not found.");
    }

    public function byId($id): ResourceInterface
    {
        $id = (int)$id;

        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        throw new RuntimeException("Sensor ID '{$id}' not found.");
    }
}