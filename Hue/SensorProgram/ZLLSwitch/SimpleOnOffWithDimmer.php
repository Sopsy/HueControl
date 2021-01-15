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
use Hue\SensorProgram\ZLLSwitch\Rules\Dimmer;
use Hue\SensorProgram\ZLLSwitch\Rules\OffPress;
use Hue\SensorProgram\ZLLSwitch\Rules\OnPress;
use InvalidArgumentException;
use function array_merge;
use function in_array;

final class SimpleOnOffWithDimmer implements ProgramInterface
{
    private GroupInterface $group;
    private SensorInterface $sensor;
    private array $resourceLinks = [];
    private array $supportedSensorModels = ['RWL020', 'RWL021'];

    public function __construct(private ApiInterface $api)
    {
    }

    public function name(): string
    {
        return 'Simple on/off with dimmer';
    }

    public function description(): string
    {
        return 'On = Last on state, others like defaults for a dimmer switch';
    }

    public function rules(): array
    {
        $this->resourceLinks[] = $this->group;
        $this->resourceLinks[] = $this->sensor;

        return array_merge(
            (new OnPress($this->sensor, new SwitchOn(), $this->group))->rules(),
            (new Dimmer($this->sensor, new SwitchUp(), $this->group, 30, 56))->rules(),
            (new Dimmer($this->sensor, new SwitchDown(), $this->group, -30, -56))->rules(),
            (new OffPress($this->sensor, new SwitchOff(), $this->group))->rules(),
        );
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