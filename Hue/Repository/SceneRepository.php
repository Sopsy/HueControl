<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\SceneInterface;
use Hue\Resource\Scene;
use InvalidArgumentException;

final class SceneRepository
{
    public function __construct(private ApiInterface $api)
    {
    }

    /**
     * @return SceneInterface[]
     */
    public function all(): array
    {
        $data = $this->api->get('/scenes');

        $return = [];
        foreach ($data->response() as $id => $scene) {
            $return[] = new Scene(
                $id,
                $scene->name,
                $scene->type,
                (int)$scene->group
            );
        }

        return $return;
    }

    public function byId(string $id): SceneInterface
    {
        $data = $this->api->get("/scenes/{$id}")->response();

        return new Scene(
            $id,
            $data->name,
            $data->type,
            (int)$data->group
        );
    }

    /**
     * @param int $group
     * @return SceneInterface[]
     */
    public function byGroupId(int $group): array
    {
        $return = [];
        foreach ($this->all() as $scene) {
            if ($scene->type() === 'GroupScene' && $scene->group() === $group) {
                $return[$scene->id()] = $scene;
            }
        }

        return $return;
    }

    public function byGroupAndName(GroupInterface $group, string $name): SceneInterface
    {
        foreach ($this->byGroupId($group->id()) as $scene) {
            if ($scene->name() === $name) {
                return $scene;
            }
        }

        throw new InvalidArgumentException("Scehe {$name} not found in group {$group}");
    }

    public function delete(string $id): void
    {
        $result = $this->api->delete('/scenes/' . $id);

        echo $result;
    }
}