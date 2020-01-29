<?php
declare(strict_types=1);

namespace Hue\SensorProgram\MotionSensor;

use Hue\Contract\Program;
use Hue\Repository\SceneRepository;

final class TimeBased extends AbstractMotionSensorProgram implements Program
{
    public function apply(): void
    {
        $this->createRulesForPresence();
        $this->createRulesForNoPresence();
    }

    private function createRulesForPresence(): void
    {
        $scenes = (new SceneRepository($this->api))->getAll();
        $energize = $scenes->byNameAndGroup('Energize', $this->groupOrLight->id());
        $concentrate = $scenes->byNameAndGroup('Concentrate', $this->groupOrLight->id());
        $read = $scenes->byNameAndGroup('Read', $this->groupOrLight->id());
        $relax = $scenes->byNameAndGroup('Relax', $this->groupOrLight->id());
        $nightlight = $scenes->byNameAndGroup('Nightlight', $this->groupOrLight->id());

        $this->addResourceLink($energize);
        $this->addResourceLink($concentrate);
        $this->addResourceLink($read);
        $this->addResourceLink($relax);
        $this->addResourceLink($nightlight);

        // 05:30 - 11:00
        $rule = $this->ruleRepo->create("Motion {$this->sensor->id()} presence morning", [
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:30:00/T11:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 11:00 - 17:00
        $rule = $this->ruleRepo->create("Motion {$this->sensor->id()} presence day", [
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T11:00:00/T17:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 17:00 - 20:00
        $rule = $this->ruleRepo->create("Motion {$this->sensor->id()} presence evening", [
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 20:00 - 23:00
        $rule = $this->ruleRepo->create("Motion {$this->sensor->id()} presence late", [
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T00:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 23:00 - 05:30
        $rule = $this->ruleRepo->create("Motion {$this->sensor->id()} presence night", [
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T00:00:00/T05:30:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForNoPresence(): void
    {
        $rule = $this->ruleRepo->create("Motion {$this->sensor->id()} no-presence", [
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'eq', 'value' => 'false'],
            ['address' => "/sensors/{$this->sensor->id()}/state/presence", 'operator' => 'ddx', 'value' => 'PT00:09:15'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}