<?php
declare(strict_types=1);

namespace Hue;

use Hue\Group\GroupGroup;
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
use const FILTER_VALIDATE_IP;

final class Bridge
{
    private $user;
    private $ip;
    private $name;

    /** @var GroupGroup */
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
        $this->ip = $bridgeIp;

        $this->loadData();
    }

    private function loadData()
    {
        $json = file_get_contents("http://{$this->ip}/api/{$this->user}/");

        if (!$json) {
            throw new RuntimeException("Could not connect to the bridge at {$this->ip}.");
        }

        $data = json_decode($json, false);

        // Bridge
        $this->name = $data->config->name;

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

            $groups[(int)$groupId] = new Group((int)$groupId, $group->name, $group->type, $group->class, $lights, $scenes);
        }

        $this->groups = new GroupGroup(...$groups);

        // ResourceInterface links
        $resourceLinks = [];
        foreach ($data->resourcelinks as $id => $resourceLink) {
            $resourceLinks[] = new ResourceLinks((int)$id, $resourceLink->name, $resourceLink->type, $resourceLink->links);
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

        echo "Groups in {$this->name}:\n\n";

        foreach ($this->groups->all() AS $group) {
            echo "{$group->id()}: {$group->name()}\n";
        }

        return ob_get_clean();
    }

    public function getSensors(): string
    {
        ob_start();

        echo "Sensors in {$this->name}:\n\n";

        foreach ($this->sensors->all() AS $sensor) {
            echo "{$sensor->id()}: {$sensor->name()} ({$sensor->type()}: {$sensor->modelId()})\n";
        }

        return ob_get_clean();
    }

    public function getScenes(string $group): string
    {
        ob_start();

        echo "Scenes in {$this->name} for {$this->groups->byName($group)->name()}:\n\n";

        foreach ($this->groups->byName($group)->scenes() AS $scene) {
            echo "{$scene->id()}: {$scene->name()}\n";
        }

        return ob_get_clean();
    }

    public function getResourceLinks(): string
    {
        ob_start();

        echo "ResourceInterface links in {$this->name}:\n\n";

        foreach ($this->resourceLinks->all() AS $resourceLink) {
            echo "{$resourceLink->id()}: {$resourceLink->name()}\n";
            foreach ($resourceLink->links() as $link) {
                echo "  - {$link}\n";
            }
            echo "\n";
        }

        return ob_get_clean();
    }

    public function programDimmerSwitch(string $switchName, string $groupName): void
    {
        $links = $this->resourceLinks->byName($switchName);
        $group = $this->groups->a;

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