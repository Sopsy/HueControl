<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\HasGamut;
use Hue\Contract\HasModel;
use Hue\Contract\TypedResourceInterface;

final class Light implements TypedResourceInterface, HasModel, HasGamut
{
    private $id;
    private $name;
    private $type;
    private $model;
    private $gamutType;

    public function __construct(int $id, string $name, string $type, string $model, string $gamutType)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->model = $model;
        $this->gamutType = $gamutType;
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

    public function model(): string
    {
        return $this->model;
    }

    public function gamutType(): string
    {
        return $this->gamutType;
    }
}