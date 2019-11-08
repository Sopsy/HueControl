<?php
declare(strict_types=1);

namespace Hue\Program\DimmerSwitch;

use Hue\Program\AbstractSwitchProgram;
use Hue\Repository\SensorRepository;

abstract class AbstractDimmerSwitchProgram extends AbstractSwitchProgram
{
    protected function switchIsCompatible(): bool
    {
        $switch = (new SensorRepository($this->api))->getAll()->byName($this->switchName);

        return $switch->type() === 'ZLLSwitch' && $switch->modelId() === 'RWL021';
    }
}