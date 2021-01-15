<?php
declare(strict_types=1);

namespace Hue\Button;

use Hue\Contract\ButtonInterface;

final class SwitchUp implements ButtonInterface
{
    public function name(): string
    {
        return 'up';
    }

    public function intialPressEvent(): string
    {
        return '2000';
    }

    public function holdEvent(): string
    {
        return '2001';
    }

    public function shortReleaseEvent(): string
    {
        return '2002';
    }

    public function longReleaseEvent(): string
    {
        return '2003';
    }
}