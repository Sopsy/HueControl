<?php
declare(strict_types=1);

namespace Hue;

use Hue\Api\Api;
use Hue\SensorProgram\DimmerSwitch;
use Hue\SensorProgram\SmartButton;
use Hue\SensorProgram\MotionSensor;
use Hue\Repository\GroupRepository;
use Hue\Repository\ResourceLinkRepository;
use Hue\Repository\SceneRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;
use ReflectionException;
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

    /**
     * @param string $sensorName
     * @param string $groupName
     * @param string $program
     * @throws ReflectionException
     */
    public function programSensor(string $sensorName, string $groupName, string $program): void
    {
        switch ($program) {
            case 'DimmerSwitch-SceneCycleWithDimmer':
                new DimmerSwitch\SceneCycleWithDimmer($this->api, $sensorName, $groupName);
                break;
            case 'DimmerSwitch-SceneTimeCycleWithDimmer':
                new DimmerSwitch\SceneTimeCycleWithDimmer($this->api, $sensorName, $groupName);
                break;
            case 'DimmerSwitch-SceneButtons':
                new DimmerSwitch\SceneButtons($this->api, $sensorName, $groupName);
                break;
            case 'DimmerSwitch-SceneButtonsWithLongOff':
                new DimmerSwitch\SceneButtonsWithLongOff($this->api, $sensorName, $groupName);
                break;
            case 'DimmerSwitch-TimeBasedWithDimmer':
                new DimmerSwitch\TimeBasedWithDimmer($this->api, $sensorName, $groupName);
                break;
            case 'SmartButton-TimeBasedWithLongOff':
                new SmartButton\TimeBasedWithLongOff($this->api, $sensorName, $groupName);
                break;
            case 'MotionSensor-TimeBased':
                new MotionSensor\TimeBased($this->api, $sensorName, $groupName);
                break;
            default;
                echo "Unknown program '{$program}'!\n";
                return;
        }

        $this->deleteUnusedMemorySensors();

        echo "Programming done!\n";
    }

    public function deleteUnusedMemorySensors(): void
    {
        echo "Deleting unused generic sensors...\n";
        (new SensorRepository($this->api))->deleteUnusedGeneric();
    }
}