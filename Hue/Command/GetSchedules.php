<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\ScheduleRepository;

final class GetSchedules implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        echo "Schedules in {$this->bridge->name()}:\n\n";

        foreach ((new ScheduleRepository($this->bridge->api()))->all() as $schedule) {
            echo "{$schedule->id()}: {$schedule->name()}\n";
            echo "  {$schedule->description()}\n";
            echo "  LocalTime: {$schedule->localTime()}\n";
            echo "  Time: {$schedule->time()}\n";
            echo "  Status: {$schedule->status()}\n";
            echo "  Command:\n";
            $command = $schedule->command();
            echo "    - {$command}\n";
        }
    }
}