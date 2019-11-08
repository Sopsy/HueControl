<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\GroupGroup;
use Hue\Group\LightGroup;
use Hue\Group\SceneGroup;
use Hue\Resource\Group;
use Hue\Resource\Light;
use Hue\Resource\Scene;

final class GroupRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $groups = ($this->api->get('/groups'))->data();
        $lights = ($this->api->get('/lights'))->data();
        $scenes = ($this->api->get('/scenes'))->data();

        $groupGroups = [];
        foreach ($groups AS $groupId => $group) {
            $groupLights = [];
            foreach ($group->lights AS $lightId) {
                $groupLights[] = new Light(
                    (int)$lightId,
                    $lights->$lightId->name,
                    $lights->$lightId->type,
                    $lights->$lightId->modelid,
                    $lights->$lightId->capabilities->control->colorgamuttype,
                    $lights->$lightId->manufacturername,
                    $lights->$lightId->productname
                );
            }
            $groupLights = new LightGroup(...$groupLights);

            $groupScenes = [];
            foreach ($scenes AS $sceneId => $scene) {
                if ($scene->type !== 'GroupScene' || $scene->group !== $groupId) {
                    continue;
                }

                $groupScenes[] = new Scene($sceneId, $scene->name, $scene->type, (int)$scene->group);
            }
            $groupScenes = new SceneGroup(...$groupScenes);

            $groupGroups[(int)$groupId] = new Group((int)$groupId, $group->name, $group->type, $group->class, $groupLights, $groupScenes);
        }

        return new GroupGroup(...$groupGroups);
    }
}