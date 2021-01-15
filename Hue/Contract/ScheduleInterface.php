<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ScheduleInterface extends ResourceInterface
{
    public function id(): int;

    public function description(): string;

    public function command(): string;

    public function time(): string;

    public function localTime(): string;

    public function status(): string;
}