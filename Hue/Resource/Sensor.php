<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\SensorInterface;

final class Sensor implements SensorInterface
{
    public function __construct(
        private int $id,
        private string $name,
        private string $type,
        private string $modelId
    ) {
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
        return $this->modelId;
    }

    public function apiUrl(): string
    {
        return "/sensors/{$this->id()}";
    }

    public function apiStateUrl(): string
    {
        return "{$this->apiUrl()}/state";
    }
}