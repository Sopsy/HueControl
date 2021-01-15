<?php
declare(strict_types=1);

namespace Hue\SensorProgram\ZLLSwitch\Rules;

use Hue\Contract\ButtonInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\ProgramRulesInterface;
use Hue\Contract\SensorInterface;
use Hue\Resource\Rule;

final class Dimmer implements ProgramRulesInterface
{
    public function __construct(
        private SensorInterface $sensor,
        private ButtonInterface $button,
        private GroupInterface $group,
        private int $initialBrightnessChange,
        private int $holdBrightnessChange
    ) {
    }

    public function rules(): array
    {
        $return = [];

        $return[] = new Rule(
            0,
            "{$this->sensor->id()} {$this->button->name()}-press dimmer",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->intialPressEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx']
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['transitiontime' => 9, 'bri_inc' => $this->initialBrightnessChange]]
            ]
        );

        $return[] = new Rule(
            0,
            "{$this->sensor->id()} {$this->button->name()}-long dimmer",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->holdEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx']
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['transitiontime' => 9, 'bri_inc' => $this->holdBrightnessChange]]
            ]
        );

        $return[] = new Rule(
            0,
            "{$this->sensor->id()} {$this->button->name()}-rele dimmer",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->longReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx']
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['bri_inc' => 0]]
            ]
        );

        return $return;
    }
}