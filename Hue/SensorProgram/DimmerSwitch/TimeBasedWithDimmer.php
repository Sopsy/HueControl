<?php
declare(strict_types=1);

namespace Hue\SensorProgram\DimmerSwitch;

use Hue\Contract\Program;
use Hue\Repository\SceneRepository;

final class TimeBasedWithDimmer extends AbstractDimmerSwitchProgram implements Program
{
    public function apply(): void
    {
        // Create rules
        $this->createRulesForOnButton();
        $this->createRulesForUpButton();
        $this->createRulesForDownButton();
        $this->createRulesForOffButton();
    }

    private function createRulesForOnButton(): void
    {
        $scenes = (new SceneRepository($this->api))->getAll();
        $energize = $scenes->byNameAndGroup('Energize', $this->groupOrLight->id());
        $concentrate = $scenes->byNameAndGroup('Concentrate', $this->groupOrLight->id());
        $read = $scenes->byNameAndGroup('Read', $this->groupOrLight->id());
        $relax = $scenes->byNameAndGroup('Relax', $this->groupOrLight->id());
        $nightlight = $scenes->byNameAndGroup('Nightlight', $this->groupOrLight->id());

        // 05:30 - 11:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press morning", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:30:00/T11:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 11:00 - 17:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press day", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T11:00:00/T17:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 17:00 - 20:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press evening", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 20:00 - 23:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press late", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T00:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 23:00 - 05:30
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press night", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T00:00:00/T05:30:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Long press = bright
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1003'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForUpButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 8,
                'bri_inc' => 25
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

    }

    private function createRulesForDownButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 8,
                'bri_inc' => -25
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForOffButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} off-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '4000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}