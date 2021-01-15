<?php
declare(strict_types=1);

namespace Hue\SensorProgram\ZLLSwitch\Rules;

use Hue\Contract\ButtonInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\ProgramRulesInterface;
use Hue\Contract\SensorInterface;
use Hue\Resource\Rule;

final class OffHold implements ProgramRulesInterface
{
    public function __construct(
        private SensorInterface $sensor,
        private ButtonInterface $button,
        private GroupInterface $group,
        private bool $anyLightsOn
    ) {
    }

    public function rules(): array
    {
        $return = [];

        $return[] = new Rule(
            0,
            "{$this->sensor->id()} {$this->button->name()}-press off",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => $this->anyLightsOn ? 'true' : 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
            ]
        );

        return $return;
    }
}