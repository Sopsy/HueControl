<?php
declare(strict_types=1);

namespace Hue\Contract;

interface PhysicalDeviceInterface extends SensorInterface
{
    public function manufacturer(): string;

    public function productName(): string;
}