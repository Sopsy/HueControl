<?php
declare(strict_types=1);

namespace Hue;

use Hue\Group\GroupGroup;
use Hue\Group\LightGroup;
use Hue\Group\ResourceLinksGroup;
use Hue\Group\RuleGroup;
use Hue\Group\SceneGroup;
use Hue\Group\SensorGroup;
use Hue\Resource\Group;
use Hue\Resource\Light;
use Hue\Resource\ResourceLinks;
use Hue\Resource\Rule;
use Hue\Resource\Scene;
use Hue\Resource\Sensor;
use InvalidArgumentException;
use function ob_get_clean;
use function str_replace;
use function strpos;
use function var_dump;
use const FILTER_VALIDATE_IP;

final class Bridge
{
    private $user;
    private $ip;
    private $name;
    private $api;

    /** @var GroupGroup */
    private $groups;
    /** @var SceneGroup */
    private $scenes;
    /** @var ResourceLinksGroup */
    private $resourceLinks;
    /** @var SensorGroup */
    private $sensors;
    /** @var RuleGroup */
    private $rules;

    public function __construct(string $bridgeIp, string $user)
    {
        if (!filter_var($bridgeIp, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid bridge IP');
        }

        $this->user = $user;
        $this->ip = $bridgeIp;
        $this->api = new Api($this->ip, $this->user);

        $this->loadData();
    }

    private function loadData()
    {
        $data = $this->api->get('/');

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

        // Rules
        $rules = [];
        foreach ($data->rules as $id => $rule) {
            $rules[] = new Rule((int)$id, $rule->name);
        }
        $this->rules = new RuleGroup(...$rules);
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

    public function programDimmerSwitch(string $switchName, string $groupName): string
    {
        $links = $this->resourceLinks->byName($switchName);
        $group = $this->groups->byName($groupName);

        $return = '';

        // Remove old rules
        foreach ($links->linksByType('rules') as $link) {
            $rule = $this->rules->byId($link);
            $this->api->delete('/rules/' . $rule->id());
            $return .= "Deleted rule for '{$switchName}': {$rule->id()} ({$rule->name()})\n";
        }

        // Remove possible old memory flags
        $return .= $this->deleteUnusedMemorySensors();



        return $return;

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

    public function deleteUnusedMemorySensors(): string
    {
        $unusedSensors = [];
        foreach ($this->sensors->all() as $sensor) {
            if (in_array($sensor->type(), ['CLIPGenericStatus', 'CLIPGenericFlag'])) {
                $unusedSensors[$sensor->id()] = $sensor;
            }
        }

        foreach ($this->resourceLinks->all() as $resourceLink) {
            foreach ($resourceLink->links() as $link) {
                if (strpos($link, '/sensors/') !== 0) {
                    continue;
                }
                $sensorId = (int)str_replace('/sensors/', '', $link);

                // Skip non-generic sensors
                if (!array_key_exists($sensorId, $unusedSensors)) {
                    continue;
                }

                unset($unusedSensors[$sensorId]);
            }
        }

        $return = '';
        foreach ($unusedSensors as $sensorId => $sensor) {
            $this->api->delete('/sensors/' . $sensorId);
            $return .= "Deleted unused sensor: {$sensor->id()} ({$sensor->name()})\n";
        }

        return $return;
    }
}