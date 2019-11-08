<?php
declare(strict_types=1);

namespace Hue\Contract;

interface HasGamut
{
    public function gamutType(): string;
}