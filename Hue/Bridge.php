<?php
declare(strict_types=1);

namespace Hue;

use Hue\Group\LightGroup;
use Hue\Group\ResourceLinksGroup;
use Hue\Group\SceneGroup;
use Hue\Group\SensorGroup;
use Hue\Resource\Group;
use Hue\Resource\Light;
use Hue\Resource\ResourceLinks;
use Hue\Resource\Scene;
use Hue\Resource\Sensor;
use InvalidArgumentException;
use RuntimeException;
use function ob_get_clean;
use function var_dump;
use const FILTER_VALIDATE_IP;

final class Bridge
{
    private $user;
    private $bridgeIp;

    /** @var Group */
    private $groups;
    /** @var SceneGroup */
    private $scenes;
    /** @var ResourceLinksGroup */
    private $resourceLinks;
    /** @var SensorGroup */
    private $sensors;

    public function __construct(string $bridgeIp, string $user)
    {
        if (!filter_var($bridgeIp, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid bridge IP');
        }

        $this->user = $user;
        $this->bridgeIp = $bridgeIp;

        $this->loadData();
    }

    private function loadData()
    {
        $json = file_get_contents("http://{$this->bridgeIp}/api/{$this->user}/");

        if (!$json) {
            throw new RuntimeException("Could not connect to the bridge at {$this->bridgeIp}.");
        }

        $data = json_decode($json, false);

        // Groups (rooms)
        $groups = [];
        foreach ($data->groups AS $groupId => $group) {
            $lights = [];
            foreach ($group->lights AS $lightId) {
                $lights[] = new Light((int)$lightId, $data->lights->$lightId->name, $data->lights->$lightId->type);
            }
            $lights = new LightGroup(...$lights);

            $scenes = [];
            foreach ($data->scenes AS $sceneId => $scene) {
                if ($scene->type !== 'GroupScene' || $scene->group !== $groupId) {
                    continue;
                }

                $scenes[] = new Scene($sceneId, $scene->name, $scene->type);
            }
            $scenes = new SceneGroup(...$scenes);

            $groups[(int)$groupId] = new Group((int)$groupId, $group->name, $group->class, $group->type, $lights, $scenes);
        }

        $this->groups = $groups;

        // Resource links
        $resourceLinks = [];
        foreach ($data->resourcelinks as $id => $resourceLink) {
            $resourceLinks[] = new ResourceLinks((int)$id, $resourceLink->name, $resourceLink->links);
        }
        $this->resourceLinks = new ResourceLinksGroup(...$resourceLinks);

        // Sensors
        $sensors = [];
        foreach ($data->sensors as $id => $sensor) {
            $sensors[] = new Sensor((int)$id, $sensor->name, $sensor->type, $sensor->modelid);
        }
        $this->sensors = new SensorGroup(...$sensors);
    }

    public function getGroups(): string
    {
        ob_start();

        foreach ($this->groups AS $group) {
            echo "{$group->id()}: {$group->name()}\n";
        }

        return ob_get_clean();
    }

    public function getSensors(): string
    {
        ob_start();

        foreach ($this->sensors->sensors() AS $sensor) {
            echo "{$sensor->id()}: {$sensor->name()} ({$sensor->type()}: {$sensor->modelId()})\n";
        }

        return ob_get_clean();
    }

    public function getScenes(int $groupId): string
    {
        ob_start();

        echo "Scenes for {$this->groups[$groupId]->name()}:\n";

        foreach ($this->groups[$groupId]->scenes() AS $scene) {
            echo "{$scene->id()}: {$scene->name()}\n";
        }

        return ob_get_clean();
    }

    public function getResourceLinks(): string
    {
        ob_start();

        echo "Resource links:\n";

        foreach ($this->resourceLinks->resourceLinks() AS $resourceLink) {
            echo "{$resourceLink->id()}: {$resourceLink->name()}\n";
            foreach ($resourceLink->links() as $link) {
                echo "  - {$link}\n";
            }
            echo "\n";
        }

        return ob_get_clean();
    }

    public function programDimmerSwitch(int $switchId, int $groupId): void
    {
        var_dump($this->resourceLinks->resourceLinksByName('Kitchen switch'));

        '
            "conditions": [
                {
                    "address": "/sensors/' . $switchId . '/state/buttonevent",
                    "operator": "eq",
                    "value": "1001"
                },
                {
                    "address": "/sensors/' . $switchId . '/state/lastupdated",
                    "operator": "dx"
                }
            ],
            "actions": [
                {
                    "address": "/groups/' . $groupId . '/action",
                    "method": "PUT",
                    "body": {
                        "on": false
                    }
                }
            ]';
    }
}