<?php
declare(strict_types=1);

namespace Hue;

final class Light
{
    private $id;
    private $name;
    private $type;

    public function __construct(int $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }
}