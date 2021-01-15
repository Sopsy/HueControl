<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\GroupInterface;
use Hue\Contract\LightInterface;
use Hue\Contract\SceneInterface;

final class Group implements GroupInterface
{
    /**
     * @param int $id
     * @param string $name
     * @param string $type
     * @param string $class
     * @param LightInterface[] $lights
     * @param SceneInterface[] $scenes
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $type,
        private string $class,
        private array $lights,
        private array $scenes
    )
    {
    }

    public function class(): string
    {
        return $this->class;
    }

    public function lights(): array
    {
        return $this->lights;
    }

    public function scenes(): array
    {
        return $this->scenes;
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

    public function apiUrl(): string
    {
        return "/groups/{$this->id()}";
    }

    public function apiStateUrl(): string
    {
        return "{$this->apiUrl()}/state";
    }
}