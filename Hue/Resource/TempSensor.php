<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\SensorInterface;
use Hue\Contract\TempSensorInterface;
use function round;

final class TempSensor implements TempSensorInterface
{
    public function __construct(
        private SensorInterface $sensor,
        private string $manufacturer,
        private string $productName,
        private int $temp
    )
    {
    }

    public function temp(): float
    {
        return round($this->temp / 100, 1);
    }

    public function id(): int
    {
        return $this->sensor->id();
    }

    public function manufacturer(): string
    {
        return $this->manufacturer;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function name(): string
    {
        return $this->sensor->name();
    }

    public function apiUrl(): string
    {
        return $this->sensor->apiUrl();
    }

    public function model(): string
    {
        return $this->sensor->model();
    }

    public function type(): string
    {
        return $this->sensor->type();
    }

    public function apiStateUrl(): string
    {
        return $this->sensor->apiStateUrl();
    }
}