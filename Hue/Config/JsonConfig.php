<?php
declare(strict_types=1);

namespace Hue\Config;

use Hue\Contract\ConfigInterface;
use InvalidArgumentException;

final class JsonConfig implements ConfigInterface
{
    private string $bridgeIp;
    private string $username;

    public function __construct(string $jsonFile)
    {
        $config = json_decode(
            file_get_contents($jsonFile),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!isset($config->bridgeIp, $config->username)) {
            throw new InvalidArgumentException("Config file '{$jsonFile}' is not in proper format.");
        }

        $this->bridgeIp = $config->bridgeIp;
        $this->username = $config->username;
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