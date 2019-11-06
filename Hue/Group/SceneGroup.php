<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Resource\Scene;
use RuntimeException;

final class SceneGroup
{
    private $items;

    public function __construct(Scene ...$scenes)
    {
        $this->items = $scenes;
    }

    public function scenes(): array
    {
        return $this->items;
    }

    public function findByName(string $name): Scene
    {
        foreach ($this->items as $item) {
            if ($item->name() === $name) {
                return $item;
            }
        }

        throw new RuntimeException("Scene {$name} not found.");
    }
}