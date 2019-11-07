<?php
declare(strict_types=1);

namespace Hue;

use Hue\Api\Api;
use Hue\Group\GroupGroup;
use Hue\Group\ResourceLinksGroup;
use Hue\Group\RuleGroup;
use Hue\Group\SceneGroup;
use Hue\Group\SensorGroup;
use Hue\Program\DimmerSwitch\BrightnessCycle;
use Hue\Repository\SensorRepository;
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

        $data = ($this->api->get('/config'))->response();
        $this->name = $data->name;
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
        $program = new BrightnessCycle($this->api, $switchName, $groupName);
        $program->apply();

        return $program->output();

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
        $sensorRepo = new SensorRepository($this->api);

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
            $sensorRepo->delete($sensorId);
            $return .= "Deleted unused sensor: {$sensor->id()} ({$sensor->name()})\n";
        }

        return $return;
    }
}