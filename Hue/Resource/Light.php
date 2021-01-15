<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\LightInterface;
use Hue\Contract\SensorInterface;

final class Light implements LightInterface
{
    public function __construct(
        private SensorInterface $sensor,
        private string $manufacturer,
        private string $productName,
        private string $gamutType,
    )
    {
    }

    public function id(): int
    {
        return $this->sensor->id();
    }

    public function name(): string
    {
        return $this->sensor->name();
    }

    public function type(): string
    {
        return $this->sensor->type();
    }

    public function model(): string
    {
        return $this->sensor->model();
    }

    public function gamutType(): string
    {
        return $this->gamutType;
    }

    public function manufacturer(): string
    {
        return $this->manufacturer;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function apiUrl(): string
    {
        return "/lights/{$this->id()}";
    }

    public function apiStateUrl(): string
    {
        return "{$this->apiUrl()}/state";
    }
}