<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Resource\Light;

final class LightGroup
{
    private $items;

    public function __construct(Light ...$lights)
    {
        $this->items = $lights;
    }

    public function lights(): array
    {
        return $this->items;
    }
}