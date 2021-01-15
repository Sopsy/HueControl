<?php
declare(strict_types=1);

namespace Hue\SensorProgram\ZLLSwitch\Rules;

use Hue\Contract\ButtonInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\ProgramRulesInterface;
use Hue\Contract\SceneInterface;
use Hue\Contract\SensorInterface;
use Hue\Resource\Rule;
use function count;

final class SceneCycle implements ProgramRulesInterface
{
    private array $scenes;

    public function __construct(
        private SensorInterface $sensor,
        private ButtonInterface $button,
        private GroupInterface $group,
        private SensorInterface $statusSensor,
        SceneInterface ...$scenes,
    )
    {
        $this->scenes = $scenes;
    }

    public function rules(): array
    {
        $return = [];

        $return[] = new Rule(
            0,
            "{$this->sensor->id()} {$this->button->name()}-press cycle-on",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['on' => true]],
                ['address' => $this->statusSensor->apiStateUrl(), 'method' => 'PUT', 'body' => ['status' => 0]],
            ]
        );

        $i = 0;
        foreach ($this->scenes as $scene) {
            $next = $i + 1;
            if (count($this->scenes) < $next) {
                $next = 0;
            }

            $return[] = new Rule(
                0,
                "Switch {$this->sensor->id()} {$this->button->name()}-press cycle-{$i}}",
                [
                    ['address' => "{$this->sensor->apiStateUrl()}/buttonevent", 'operator' => 'eq', 'value' => $this->button->shortReleaseEvent()],
                    ['address' => "{$this->sensor->apiStateUrl()}/lastupdated", 'operator' => 'dx'],
                    ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'true'],
                    ['address' => "{$this->statusSensor->apiStateUrl()}/status", 'operator' => 'eq', 'value' => $i],
                ], [
                    ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $scene->id()]],
                    ['address' => "{$this->statusSensor->apiStateUrl()}", 'method' => 'PUT', 'body' => ['status' => $next]],
                ]
            );

            ++$i;
        }

        return $return;
    }
}