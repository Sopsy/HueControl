<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\ProgramInterface;
use Hue\SensorProgram\ZLLPresence\TimeBased;
use Hue\SensorProgram\ZLLSwitch\SceneButtons;
use Hue\SensorProgram\ZLLSwitch\SceneButtonsWithLongOff;
use Hue\SensorProgram\ZLLSwitch\SceneCycleWithDimmer;
use Hue\SensorProgram\ZLLSwitch\SceneTimeCycleWithDimmer;
use Hue\SensorProgram\ZLLSwitch\SimpleOnOffWithDimmer;
use Hue\SensorProgram\ZLLSwitch\TimeBasedWithDimmer;
use InvalidArgumentException;
use function array_key_exists;

final class ProgramRepository
{
    public function __construct(private ApiInterface $api)
    {
    }

    /**
     * @param string $sensorType
     * @return ProgramInterface[]
     */
    public function all(string $sensorType): array
    {
        $programs = [
            'ZLLSwitch' => [
                new SceneButtons($this->api),
                new SceneButtonsWithLongOff($this->api),
                new SceneCycleWithDimmer($this->api),
                new SceneTimeCycleWithDimmer($this->api),
                new SimpleOnOffWithDimmer($this->api),
                new TimeBasedWithDimmer($this->api),
            ],
            'ZLLPresence' => [
                new TimeBased($this->api)
            ],
        ];

        if (!array_key_exists($sensorType, $programs)) {
            throw new InvalidArgumentException("Unsupported sensor type {$sensorType}");
        }

        return $programs[$sensorType];
    }
}