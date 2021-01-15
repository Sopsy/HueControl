<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\SceneInterface;

final class Scene implements SceneInterface
{
    public function __construct(
        private string $id,
        private string $name,
        private string $type,
        private int $group
    ) {
    }

    public function id(): string
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

    public function group(): int
    {
        return $this->group;
    }

    public function apiUrl(): string
    {
        return "/scenes/{$this->id()}";
    }
}