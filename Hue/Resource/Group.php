<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Group\LightGroup;
use Hue\Group\SceneGroup;
use Hue\Resource\Scene;

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

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function lights(): array
    {
        return $this->lights->lights();
    }

    public function scenes(): array
    {
        return $this->scenes->scenes();
    }

    public function findScene(string $name): Scene
    {
        return $this->scenes->findByName($name);
    }
}