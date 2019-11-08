<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\SensorProgram\DimmerSwitch;
use Hue\SensorProgram\SmartButton;
use Hue\SensorProgram\MotionSensor;

final class ProgramSensor
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(...$args): void
    {
        if (empty($args[0])) {
            echo 'Missing sensor name';

            return;
        }

        if (empty($args[1])) {
            echo 'Missing group (room) name';

            return;
        }

        if (empty($args[2])) {
            echo 'Missing program name';

            return;
        }

        [$sensorName, $groupName, $program] = $args;

        switch ($program) {
            case 'DimmerSwitch-SceneCycleWithDimmer':
                new DimmerSwitch\SceneCycleWithDimmer($this->bridge->api(), $sensorName, $groupName);
                break;
            case 'DimmerSwitch-SceneTimeCycleWithDimmer':
                new DimmerSwitch\SceneTimeCycleWithDimmer($this->bridge->api(), $sensorName, $groupName);
                break;
            case 'DimmerSwitch-SceneButtons':
                new DimmerSwitch\SceneButtons($this->bridge->api(), $sensorName, $groupName);
                break;
            case 'DimmerSwitch-SceneButtonsWithLongOff':
                new DimmerSwitch\SceneButtonsWithLongOff($this->bridge->api(), $sensorName, $groupName);
                break;
            case 'DimmerSwitch-TimeBasedWithDimmer':
                new DimmerSwitch\TimeBasedWithDimmer($this->bridge->api(), $sensorName, $groupName);
                break;
            case 'DimmerSwitch-SimpleOnOffWithDimmer':
                new DimmerSwitch\SimpleOnOffWithDimmer($this->bridge->api(), $sensorName, $groupName);
                break;
            case 'SmartButton-TimeBasedWithLongOff':
                new SmartButton\TimeBasedWithLongOff($this->bridge->api(), $sensorName, $groupName);
                break;
            case 'MotionSensor-TimeBased':
                new MotionSensor\TimeBased($this->bridge->api(), $sensorName, $groupName);
                break;
            default;
                echo "Unknown program '{$program}'!\n";
                return;
        }

        (new DeleteUnusedMemorySensors($this->bridge))->handle(...$args);

        echo "Programming done!\n";
    }
}