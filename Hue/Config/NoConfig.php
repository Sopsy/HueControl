<?php
declare(strict_types=1);

namespace Hue\Config;

use Hue\Contract\ConfigInterface;

final class NoConfig implements ConfigInterface
{
    public function bridgeIp(): string
    {
        return '';
    }

    public function username(): string
    {
        return '';
    }

    public function isConfigured(): bool
    {
        return false;
    }
}