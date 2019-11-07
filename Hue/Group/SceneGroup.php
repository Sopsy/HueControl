<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Contract\GroupInterface;
use Hue\Contract\ResourceInterface;
use Hue\Resource\Scene;
use RuntimeException;

final class SceneGroup implements GroupInterface
{
    private $items;

    public function __construct(Scene ...$scenes)
    {
        $this->items = $scenes;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function byName($name): ResourceInterface
    {
        foreach ($this->items as $item) {
            if ($item->name() === $name) {
                return $item;
            }
        }

        throw new RuntimeException("Scene '{$name}' not found.");
    }

    public function byNameAndGroup(string $name, int $group): ResourceInterface
    {
        foreach ($this->items as $item) {
            if ($item->name() === $name && $item->group() === $group) {
                return $item;
            }
        }

        throw new RuntimeException("Scene '{$name}' for group '{$group}' not found.");
    }

    public function byId($id): ResourceInterface
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        throw new RuntimeException("Scene ID '{$id}' not found.");
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