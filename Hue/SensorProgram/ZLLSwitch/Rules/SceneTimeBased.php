<?php
declare(strict_types=1);

namespace Hue\SensorProgram\ZLLSwitch\Rules;

use Hue\Contract\ButtonInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\ProgramRulesInterface;
use Hue\Contract\SceneInterface;
use Hue\Contract\SensorInterface;
use Hue\Resource\Rule;

final class SceneTimeBased implements ProgramRulesInterface
{
    public function __construct(
        private SensorInterface $sensor,
        private ButtonInterface $button,
        private GroupInterface $group,
        private SceneInterface $morningScene,
        private SceneInterface $dayScene,
        private SceneInterface $eveningScene,
        private SceneInterface $lateScene,
        private SceneInterface $nightScene,
    )
    {
    }

    public function rules(): array
    {
        $return = [];

        // 05:00 - 10:00
        $return[] = new Rule(
            0,
            "Switch {$this->sensor->id()} {$this->button->name()}-press morning",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:00:00/T10:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->morningScene->id()]],
            ]
        );

        // 10:00 - 17:00
        $return[] = new Rule(
            0,
            "Switch {$this->sensor->id()} {$this->button->name()}-press day",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T10:00:00/T17:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->dayScene->id()]],
            ]
        );

        // 17:00 - 20:00
        $return[] = new Rule(
            0,
            "Switch {$this->sensor->id()} {$this->button->name()}-press evening",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->eveningScene->id()]],
            ]
        );

        // 20:00 - 23:00
        $return[] = new Rule(
            0,
            "Switch {$this->sensor->id()} {$this->button->name()}-press late",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T23:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->lateScene->id()]],
            ]
        );
        
        // 23:00 - 05:00
        $return[] = new Rule(
            0,
            "Switch {$this->sensor->id()} {$this->button->name()}-press night",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T23:00:00/T05:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->nightScene->id()]],
            ]
        );

        return $return;
    }
}