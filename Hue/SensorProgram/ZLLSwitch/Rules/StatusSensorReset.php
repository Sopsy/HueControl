<?php
declare(strict_types=1);

namespace Hue\SensorProgram\ZLLSwitch\Rules;

use Hue\Contract\ButtonInterface;
use Hue\Contract\ProgramRulesInterface;
use Hue\Contract\SensorInterface;
use Hue\Resource\Rule;

final class StatusSensorReset implements ProgramRulesInterface
{
    public function __construct(
        private SensorInterface $sensor,
        private ButtonInterface $button,
        private SensorInterface $statusSensor,
    )
    {
    }

    public function rules(): array
    {
        $return = [];

        $return[] = new Rule(
            0,
            "{$this->sensor->id()} {$this->button->name()}-press status-reset",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
            ], [
                ['address' => $this->statusSensor->apiStateUrl(), 'method' => 'PUT', 'body' => ['status' => 0]],
            ]
        );

        return $return;
    }
}