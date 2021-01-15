<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Resource\Group;

final class GroupRepository
{
    public function __construct(private ApiInterface $api)
    {
    }

    /**
     * @return Group[]
     */
    public function all(): array
    {
        $groups = $this->api->get('/groups')->response();
        $lights = new LightRepository($this->api);
        $scenes = new SceneRepository($this->api);

        $return = [];
        foreach ($groups as $groupId => $group) {
            $groupLights = [];
            foreach ($group->lights as $lightId) {
                $groupLights[(int)$lightId] = $lights->byId((int)$lightId);
            }

            $return[(int)$groupId] = new Group(
                (int)$groupId,
                $group->name,
                $group->type,
                $group->class,
                $groupLights,
                $scenes->byGroupId((int)$groupId)
            );
        }

        return $return;
    }

    public function byId(int $id): Group
    {

    }
}