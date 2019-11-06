<?php
declare(strict_types=1);

namespace Hue;

final class Scene
{
    private $id;
    private $name;

    public function __construct(string $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}