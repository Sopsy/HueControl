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
use Hue\Repository\SensorRepository;
use Hue\SensorProgram\ZLLSwitch\Rules\Dimmer;
use Hue\SensorProgram\ZLLSwitch\Rules\OffPress;
use Hue\SensorProgram\ZLLSwitch\Rules\SceneCycle;
use Hue\SensorProgram\ZLLSwitch\Rules\StatusSensorReset;
use InvalidArgumentException;
use function array_merge;
use function in_array;

final class SceneCycleWithDimmer implements ProgramInterface
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
        return 'Scene cycle with dimmer';
    }

    public function description(): string
    {
        return 'Like the Hue default program for a dimmer switch';
    }

    public function rules(): array
    {
        $scenes = new SceneRepository($this->api);
        $concentrate = $scenes->byGroupAndName($this->group, 'Concentrate');
        $relax = $scenes->byGroupAndName($this->group, 'Relax');
        $nightlight = $scenes->byGroupAndName($this->group, 'Nightlight');

        $this->resourceLinks[] = $this->group;
        $this->resourceLinks[] = $this->sensor;
        $this->resourceLinks[] = $concentrate;
        $this->resourceLinks[] = $relax;
        $this->resourceLinks[] = $nightlight;

        // Create status flag for repeated presses
        $statusSensor = (new SensorRepository($this->api))->createStatus("Switch {$this->sensor->id()} cycle status");
        $this->resourceLinks[] = $statusSensor;

        $offButton = new SwitchOff();
        return array_merge(
            (new SceneCycle($this->sensor, new SwitchOn(), $this->group, $statusSensor, $concentrate, $relax, $nightlight)),
            (new Dimmer($this->sensor, new SwitchUp(), $this->group, 30, 56))->rules(),
            (new Dimmer($this->sensor, new SwitchDown(), $this->group, -30, -56))->rules(),
            (new OffPress($this->sensor, new SwitchOff(), $this->group)),
            (new StatusSensorReset($this->sensor, new SwitchOff(), $statusSensor)),
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