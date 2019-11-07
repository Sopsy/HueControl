<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ResourceInterface
{
    /**
     * @return int|string
     */
    public function id();

    public function name(): string;
}