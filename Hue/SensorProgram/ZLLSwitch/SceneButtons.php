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
use Hue\SensorProgram\ZLLSwitch\Rules\OffPress;
use Hue\SensorProgram\ZLLSwitch\Rules\SceneShortPress;
use InvalidArgumentException;
use function array_merge;
use function in_array;

final class SceneButtons implements ProgramInterface
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
        return 'Scene buttons';
    }

    public function description(): string
    {
        return 'On = Concentrate, Dim up = Relax, Dim down = Nightlight, Off = Group off';
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

        return array_merge(
            (new SceneShortPress($this->sensor, new SwitchOn(), $this->group, $concentrate))->rules(),
            (new SceneShortPress($this->sensor, new SwitchUp(), $this->group, $relax))->rules(),
            (new SceneShortPress($this->sensor, new SwitchDown(), $this->group, $nightlight))->rules(),
            (new OffPress($this->sensor, new SwitchOff(), $this->group))->rules()
        );
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

    public function resourceLinks(): array
    {
        return $this->resourceLinks;
    }
}