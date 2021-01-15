<?php
declare(strict_types=1);

namespace Hue\Contract;

interface HasState
{
    public function apiStateUrl(): string;
}