<?php
declare(strict_types=1);

namespace Hue\Contract;

interface HasSetStateUrl
{
    public function apiSetStateUrl(): string;
}