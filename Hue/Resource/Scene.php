<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\TypedResourceInterface;

final class Scene implements TypedResourceInterface
{
    private $id;
    private $name;
    private $type;
    private $group;

    public function __construct(string $id, string $name, string $type, int $group)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->group = $group;
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