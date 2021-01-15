<?php
declare(strict_types=1);

namespace Hue\Contract;

interface LightInterface extends PhysicalDeviceInterface, HasState
{
    public function gamutType(): string;
}