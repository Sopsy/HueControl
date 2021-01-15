<?php
declare(strict_types=1);

namespace Hue\Contract;

interface SensorInterface extends TypedResourceInterface, HasState
{
    public function id(): int;

    public function model(): string;
}