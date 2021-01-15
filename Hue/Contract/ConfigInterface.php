<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ConfigInterface
{
    public function bridgeIp(): string;

    public function username(): string;

    public function isConfigured(): bool;
}