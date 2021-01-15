<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\ScheduleInterface;

final class Schedule implements ScheduleInterface
{
    public function __construct(
        private int $id,
        private string $name,
        private string $description,
        private string $command,
        private string $localTime,
        private string $time,
        private string $status,
    )
    {
    }

    public function description(): string
    {
        return $this->description;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function localTime(): string
    {
        return $this->localTime;
    }

    public function time(): string
    {
        return $this->time;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function apiUrl(): string
    {
        return "/schedules/{$this->id()}";
    }
}