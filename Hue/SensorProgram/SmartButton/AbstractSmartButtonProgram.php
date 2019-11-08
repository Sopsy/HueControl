<?php
declare(strict_types=1);

namespace Hue\SensorProgram\SmartButton;

use Hue\SensorProgram\AbstractSensorProgram;

abstract class AbstractSmartButtonProgram extends AbstractSensorProgram
{
    protected function sensorIsCompatible(): bool
    {
        return $this->sensor->type() === 'ZLLSwitch' && $this->sensor->modelId() === 'ROM001';
    }
}