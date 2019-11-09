<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\TypedResourceInterface;

abstract class Sensor implements TypedResourceInterface
{
    protected $id;
    protected $name;
    protected $type;
    protected $modelId;

    public function __construct(int $id, string $name, string $type, string $modelId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->modelId = $modelId;
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

    public function modelId(): string
    {
        return $this->modelId;
    }

    public function apiUrl(): string
    {
        return "/sensors/{$this->id()}";
    }
}