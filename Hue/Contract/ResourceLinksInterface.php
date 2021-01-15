<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ResourceLinksInterface extends TypedResourceInterface
{
    public function links(): array;
}