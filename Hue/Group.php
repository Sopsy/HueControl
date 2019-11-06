<?php
declare(strict_types=1);

namespace Hue;

final class Group
{
    private $id;
    private $name;
    private $class;
    private $type;
    private $lights;
    private $scenes;

    public function __construct(int $id, string $name, string $class, string $type, LightGroup $lights, SceneGroup $scenes)
    {
        $this->id = $id;
        $this->name = $name;
        $this->class = $class;
        $this->type = $type;
        $this->lights = $lights;
        $this->scenes = $scenes;
    }

    public function id(): string
    {
        return $this->name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function lights(): LightGroup
    {
        return $this->lights;
    }

    public function scenes(): SceneGroup
    {
        return $this->scenes;
    }
}