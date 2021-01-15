<?php
declare(strict_types=1);

namespace Hue\Contract;

interface CommandInterface
{
    public function run(string ...$args): void;
}