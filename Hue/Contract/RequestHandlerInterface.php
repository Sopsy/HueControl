<?php
declare(strict_types=1);

namespace Hue\Contract;

interface RequestHandlerInterface
{
    public function handle(string ...$args): void;
}