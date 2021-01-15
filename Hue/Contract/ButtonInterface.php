<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ButtonInterface
{
    public function name(): string;

    public function intialPressEvent(): string;

    public function holdEvent(): string;

    public function shortReleaseEvent(): string;

    public function longReleaseEvent(): string;
}