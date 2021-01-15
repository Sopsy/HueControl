<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\LightInterface;
use Hue\Resource\Light;
use Hue\Resource\Sensor;
use stdClass;

final class LightRepository
{
    public function __construct(private ApiInterface $api)
    {
    }

    /**
     * @return LightInterface[]
     */
    public function all(): array
    {
        $lights = $this->api->get('/lights')->response();

        $return = [];
        foreach ($lights as $lightId => $light) {
            $return[] = $this->createObject((int)$lightId, $light);
        }
        return $return;
    }

    public function byId(int $id): LightInterface
    {
        $light = $this->api->get("/lights/{$id}")->response();

        return $this->createObject($id, $light);
    }

    private function createObject(int $id, stdClass $light): LightInterface
    {
        return new Light(
            new Sensor($id, $light->name, $light->type, $light->modelid),
            $light->manufacturername,
            $light->productname,
            $light->capabilities->control->colorgamuttype
        );
    }
}