<?php
declare(strict_types=1);

namespace Hue;

use Hue\Api\Api;
use Hue\Program\DimmerSwitch\BrightnessCycle;
use Hue\Repository\GroupRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;
use function ob_get_clean;
use const FILTER_VALIDATE_IP;

final class Bridge
{
    private $user;
    private $ip;
    private $name;
    private $api;

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

    public function getScenes(string $group): void
    {
        $groupRepo = new GroupRepository($this->api);

        echo "Scenes in {$this->name} for {$group}:\n\n";

        foreach ($groupRepo->getAll()->byName($group)->scenes() AS $scene) {
            echo "{$scene->id()}: {$scene->name()}\n";
        }
    }

    public function getResourceLinks(): void
    {
        echo "ResourceInterface links in {$this->name}:\n\n";

        foreach ($this->resourceLinks->all() AS $resourceLink) {
            echo "{$resourceLink->id()}: {$resourceLink->name()}\n";
            foreach ($resourceLink->links() as $link) {
                echo "  - {$link}\n";
            }
            echo "\n";
        }
    }

    public function programDimmerSwitch(string $switchName, string $groupName): void
    {
        $this->deleteUnusedMemorySensors();

        $program = new BrightnessCycle($this->api, $switchName, $groupName);
        $program->apply();

        return;
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

    public function deleteUnusedMemorySensors(): void
    {
        echo "Deleting unused generic sensors...\n";
        (new SensorRepository($this->api))->deleteUnusedGeneric();
    }
}