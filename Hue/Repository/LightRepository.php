<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\LightGroup;
use Hue\Resource\Light;

final class LightRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $lights = ($this->api->get('/lights'))->data();

        $groupLights = [];
        foreach ($lights AS $lightId => $light) {
            $groupLights[] = new Light(
                (int)$lightId,
                $light->name,
                $light->type,
                $light->modelid,
                $light->capabilities->control->colorgamuttype,
                $light->manufacturername,
                $light->productname
            );
        }
        return new LightGroup(...$groupLights);
    }
}