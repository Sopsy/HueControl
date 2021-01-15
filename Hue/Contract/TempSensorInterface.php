<?php
declare(strict_types=1);

namespace Hue\Contract;

interface TempSensorInterface extends PhysicalDeviceInterface
{
    public function temp(): float;
}