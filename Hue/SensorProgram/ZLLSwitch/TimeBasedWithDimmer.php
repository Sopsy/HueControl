<?php
declare(strict_types=1);

namespace Hue\SensorProgram\ZLLSwitch;

use Hue\Button\SwitchDown;
use Hue\Button\SwitchOff;
use Hue\Button\SwitchOn;
use Hue\Button\SwitchUp;
use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\ProgramInterface;
use Hue\Contract\SensorInterface;
use Hue\Repository\SceneRepository;
use Hue\SensorProgram\ZLLSwitch\Rules\Dimmer;
use Hue\SensorProgram\ZLLSwitch\Rules\LongPressAllOff;
use Hue\SensorProgram\ZLLSwitch\Rules\LongPressAllOffGroupConditional;
use Hue\SensorProgram\ZLLSwitch\Rules\OffHold;
use Hue\SensorProgram\ZLLSwitch\Rules\OffPress;
use Hue\SensorProgram\ZLLSwitch\Rules\SceneHold;
use Hue\SensorProgram\ZLLSwitch\Rules\SceneTimeBased;
use InvalidArgumentException;
use function array_merge;
use function in_array;

final class TimeBasedWithDimmer implements ProgramInterface
{
    private GroupInterface $group;
    private SensorInterface $sensor;
    private array $resourceLinks = [];
    private array $supportedSensorModels = ['ROM001', 'RWL020', 'RWL021'];

    public function __construct(private ApiInterface $api)
    {
    }

    public function name(): string
    {
        return 'Time based with dimmer';
    }

    public function description(): string
    {
        return 'On = Different color temperature depending on the time of day, others like defaults for a dimmer switch';
    }

    public function rules(): array
    {
        $scenes = new SceneRepository($this->api);
        $energize = $scenes->byGroupAndName($this->group, 'Energize');
        $concentrate = $scenes->byGroupAndName($this->group, 'Concentrate');
        $read = $scenes->byGroupAndName($this->group, 'Read');
        $relax = $scenes->byGroupAndName($this->group, 'Relax');
        $nightlight = $scenes->byGroupAndName($this->group, 'Nightlight');

        $this->resourceLinks[] = $this->group;
        $this->resourceLinks[] = $this->sensor;
        $this->resourceLinks[] = $energize;
        $this->resourceLinks[] = $concentrate;
        $this->resourceLinks[] = $read;
        $this->resourceLinks[] = $relax;
        $this->resourceLinks[] = $nightlight;

        $return = array_merge(
            (new SceneTimeBased($this->sensor, new SwitchOn(), $this->group, $energize, $concentrate, $relax, $relax, $nightlight))->rules(),
            (new SceneHold($this->sensor, new SwitchOn(), $this->group, $concentrate, false))->rules(),
            (new OffHold($this->sensor, new SwitchOn(), $this->group,true))->rules(),
        );

        if ($this->sensor->model() !== 'ROM001') {
            $return = array_merge(
                $return,
                (new Dimmer($this->sensor, new SwitchUp(), $this->group, 30, 56))->rules(),
                (new Dimmer($this->sensor, new SwitchDown(), $this->group, -30, -56))->rules(),
                (new OffPress($this->sensor, new SwitchOff(), $this->group))->rules(),
                (new LongPressAllOff($this->sensor, new SwitchOff()))->rules(),
            );
        } else {
            $return = array_merge(
                $return,
                (new LongPressAllOffGroupConditional($this->sensor, new SwitchOn(), $this->group, true))->rules(),
            );
        }

        return $return;
    }

    public function resourceLinks(): array
    {
        return $this->resourceLinks;
    }

    public function withGroupAndSensor(GroupInterface $group, SensorInterface $sensor): ProgramInterface
    {
        $clone = clone $this;
        $clone->group = $group;
        $clone->sensor = $sensor;

        if (!in_array($sensor->model(), $this->supportedSensorModels, true)) {
            throw new InvalidArgumentException("Program '{$this->name()}' does not support sensor model {$sensor->model()}.");
        }

        return $clone;
    }
}