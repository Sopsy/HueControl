<?php
declare(strict_types=1);

namespace Hue;

use Hue\Api\Api;
use Hue\Contract\ApiInterface;
use InvalidArgumentException;
use const FILTER_VALIDATE_IP;

final class Bridge
{
    private $user;
    private $ip;
    private $name;
    private $api;

    public function __construct(string $bridgeIp, string $user)
    {
        if (!filter_var($bridgeIp, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid bridge IP');
        }

        $this->user = $user;
        $this->ip = $bridgeIp;
        $this->api = new Api($this->ip, $this->user);

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