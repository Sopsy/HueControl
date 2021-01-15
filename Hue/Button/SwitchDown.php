<?php
declare(strict_types=1);

namespace Hue\Button;

use Hue\Contract\ButtonInterface;

final class SwitchDown implements ButtonInterface
{
    public function name(): string
    {
        return 'down';
    }

    public function intialPressEvent(): string
    {
        return '3000';
    }

    public function holdEvent(): string
    {
        return '3001';
    }

    public function shortReleaseEvent(): string
    {
        return '3002';
    }

    public function longReleaseEvent(): string
    {
        return '3003';
    }
}