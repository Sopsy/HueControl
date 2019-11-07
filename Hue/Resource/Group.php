<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\ResourceInterface;
use Hue\Contract\TypedResourceInterface;
use Hue\Group\LightGroup;
use Hue\Group\SceneGroup;

final class Group implements TypedResourceInterface
{
    private $id;
    private $name;
    private $type;
    private $class;
    private $lights;
    private $scenes;

    public function __construct(int $id, string $name, string $type, string $class, LightGroup $lights, SceneGroup $scenes)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
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

    public function type(): string
    {
        return $this->type;
    }

    public function lights(): array
    {
        return $this->lights->lights();
    }

    /**
     * @return ResourceInterface[]
     */
    public function scenes(): array
    {
        return $this->scenes->all();
    }

    public function findScene(string $name): ResourceInterface
    {
        return $this->scenes->byName($name);
    }
}