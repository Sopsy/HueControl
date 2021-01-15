<?php
declare(strict_types=1);

namespace Hue\Config;

use Hue\Contract\ConfigInterface;

final class CustomConfig implements ConfigInterface
{
    public function __construct(
        private string $bridgeIp,
        private string $username
    ) {
    }

    public function bridgeIp(): string
    {
        return $this->bridgeIp;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function isConfigured(): bool
    {
        return true;
    }
}