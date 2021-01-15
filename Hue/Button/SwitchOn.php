<?php
declare(strict_types=1);

namespace Hue\Button;

use Hue\Contract\ButtonInterface;

final class SwitchOn implements ButtonInterface
{
    public function name(): string
    {
        return 'on';
    }

    public function intialPressEvent(): string
    {
        return '1000';
    }

    public function holdEvent(): string
    {
        return '1001';
    }

    public function shortReleaseEvent(): string
    {
        return '1002';
    }

    public function longReleaseEvent(): string
    {
        return '1003';
    }
}