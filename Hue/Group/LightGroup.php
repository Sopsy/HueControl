<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Contract\GroupInterface;
use Hue\Contract\ResourceInterface;
use Hue\Resource\Light;
use RuntimeException;

final class LightGroup implements GroupInterface
{
    private $items;

    public function __construct(Light ...$lights)
    {
        $this->items = $lights;
    }

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

        throw new RuntimeException("Light '{$name}' not found.");
    }

    public function byId($id): ResourceInterface
    {
        $id = (int)$id;

        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        throw new RuntimeException("Light ID '{$id}' not found.");
    }

    public function idExists($id): bool
    {
        foreach ($this->items AS $item) {
            if ($item->id() === $id) {
                return true;
            }
        }

        return false;
    }

    public function nameExists($name): bool
    {
        foreach ($this->items AS $item) {
            if ($item->name() === $name) {
                return true;
            }
        }

        return false;
    }
}