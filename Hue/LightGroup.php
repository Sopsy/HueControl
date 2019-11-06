<?php
declare(strict_types=1);

namespace Hue;

final class LightGroup extends AbstractGroup
{
    public function __construct(Light ...$lights)
    {
        $this->items = $lights;
    }

    public function lights(): array
    {
        return $this->items;
    }
}