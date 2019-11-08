<?php
declare(strict_types=1);

namespace Hue\SensorProgram\MotionSensor;

use Hue\SensorProgram\AbstractSensorProgram;
use function in_array;

abstract class AbstractMotionSensorProgram extends AbstractSensorProgram
{
    protected function sensorIsCompatible(): bool
    {
        return $this->sensor->type() === 'ZLLPresence' && in_array($this->sensor->modelId(), ['SML001', 'SML002']);
    }
}