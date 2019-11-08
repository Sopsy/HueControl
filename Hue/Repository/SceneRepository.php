<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\SceneGroup;
use Hue\Resource\Scene;

final class SceneRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $data = ($this->api->get('/scenes'))->data();

        $scenes = [];
        foreach ($data as $id => $scene) {
            $scenes[] = new Scene($id, $scene->name, $scene->type, (int)$scene->group);
        }

        return new SceneGroup(...$scenes);
    }

    public function delete(string $id): void
    {
        $result = $this->api->delete('/scenes/' . $id);

        echo $result;
    }
}