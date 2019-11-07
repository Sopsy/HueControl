<?php
declare(strict_types=1);

namespace Hue;

use Hue\Api\Api;
use Hue\Program\DimmerSwitch\SceneButtons;
use Hue\Program\DimmerSwitch\SceneButtonsWithLongOff;
use Hue\Program\DimmerSwitch\SceneCycleWithDimmer;
use Hue\Program\DimmerSwitch\SceneTimeCycleWithDimmer;
use Hue\Repository\GroupRepository;
use Hue\Repository\ResourceLinkRepository;
use Hue\Repository\SceneRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;
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

    public function getGroups(): void
    {
        echo "Groups in {$this->name}:\n\n";

        foreach ((new GroupRepository($this->api))->getAll()->all() AS $group) {
            echo "{$group->id()}: {$group->name()}\n";
        }
    }

    public function getSensors(): void
    {
        echo "Sensors in {$this->name}:\n\n";

        foreach ((new SensorRepository($this->api))->getAll()->all() AS $sensor) {
            echo "{$sensor->id()}: {$sensor->name()} ({$sensor->type()}: {$sensor->modelId()})\n";
        }
    }

    public function getScenes(?string $group = null): void
    {
        if ($group !== null) {
            echo "Scenes in {$this->name} for {$group}:\n\n";

            foreach ((new GroupRepository($this->api))->getAll()->byName($group)->scenes() AS $scene) {
                echo "Group {$scene->group()}: {$scene->id()} ({$scene->name()})\n";
            }
        } else {
            echo "Scenes in {$this->name}:\n\n";

            foreach ((new SceneRepository($this->api))->getAll()->all() AS $scene) {
                echo "Group {$scene->group()}: {$scene->id()} ({$scene->name()})\n";
            }
        }
    }

    public function getResourceLinks(): void
    {
        echo "ResourceInterface links in {$this->name}:\n\n";

        foreach ((new ResourceLinkRepository($this->api))->getAll()->all() AS $resourceLink) {
            echo "{$resourceLink->id()}: {$resourceLink->name()}\n";
            foreach ($resourceLink->links() as $link) {
                echo "  - {$link}\n";
            }
            echo "\n";
        }
    }

    public function programDimmerSwitch(string $switchName, string $groupName, string $program): void
    {
        switch ($program) {
            case 'SceneCycleWithDimmer':
                $programClass = new SceneCycleWithDimmer($this->api, $switchName, $groupName);
                break;
            case 'SceneTimeCycleWithDimmer':
                $programClass = new SceneTimeCycleWithDimmer($this->api, $switchName, $groupName);
                break;
            case 'SceneButtons':
                $programClass = new SceneButtons($this->api, $switchName, $groupName);
                break;
            case 'SceneButtonsWithLongOff':
                $programClass = new SceneButtonsWithLongOff($this->api, $switchName, $groupName);
                break;
            default;
                echo "Unknown program '{$program}'!\n";
                return;
        }

        $programClass->apply();

        $this->deleteUnusedMemorySensors();

        echo "Programming done!\n";
    }

    public function deleteUnusedMemorySensors(): void
    {
        echo "Deleting unused generic sensors...\n";
        (new SensorRepository($this->api))->deleteUnusedGeneric();
    }
}