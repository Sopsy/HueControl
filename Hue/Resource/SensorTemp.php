<?php
declare(strict_types=1);

namespace Hue\Resource;

final class SensorTemp extends Sensor
{
    private $temp;

    public function __construct(int $id, string $name, string $type, string $modelId, float $temp)
    {
        parent::__construct($id, $name, $type, $modelId);
        $this->temp = $temp/100;
    }

    public function temp(): float
    {
        return $this->temp;
    }
}