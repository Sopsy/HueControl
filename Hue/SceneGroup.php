<?php
declare(strict_types=1);

namespace Hue;

final class SceneGroup extends AbstractGroup
{
    public function __construct(Scene ...$scenes)
    {
        $this->items = $scenes;
    }

    public function scenes(): array
    {
        return $this->items;
    }
}