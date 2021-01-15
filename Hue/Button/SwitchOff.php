<?php
declare(strict_types=1);

namespace Hue\Button;

use Hue\Contract\ButtonInterface;

final class SwitchOff implements ButtonInterface
{
    public function name(): string
    {
        return 'off';
    }

    public function intialPressEvent(): string
    {
        return '4000';
    }

    public function holdEvent(): string
    {
        return '4001';
    }

    public function shortReleaseEvent(): string
    {
        return '4002';
    }

    public function longReleaseEvent(): string
    {
        return '4003';
    }
}