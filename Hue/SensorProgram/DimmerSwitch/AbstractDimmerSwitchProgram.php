<?php
declare(strict_types=1);

namespace Hue\SensorProgram\DimmerSwitch;

use Hue\SensorProgram\AbstractSensorProgram;

abstract class AbstractDimmerSwitchProgram extends AbstractSensorProgram
{
    protected function sensorIsCompatible(): bool
    {
        return $this->sensor->type() === 'ZLLSwitch' && $this->sensor->modelId() === 'RWL021';
    }
}