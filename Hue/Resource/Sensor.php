<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\TypedResourceInterface;

final class Sensor implements TypedResourceInterface
{
    private $id;
    private $name;
    private $type;
    private $modelId;

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
}