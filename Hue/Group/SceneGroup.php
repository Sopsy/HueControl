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

        throw new RuntimeException("Scene '{$name}'' not found.");
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
}