<?php
declare(strict_types=1);

namespace Hue;

use Hue\Api\Api;
use Hue\Contract\ApiInterface;
use Hue\Contract\ConfigInterface;
use InvalidArgumentException;
use const FILTER_VALIDATE_IP;

final class Bridge
{
    private string $name;
    private ApiInterface $api;

    public function __construct(private ConfigInterface $config)
    {
        if (!filter_var($this->config->bridgeIp(), FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid bridge IP');
        }

        $this->api = new Api($this->config);

        $data = ($this->api->get('/config'))->response();
        $this->name = $data->name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function api(): ApiInterface
    {
        return $this->api;
    }
}