<?php
declare(strict_types=1);

namespace Hue\SensorProgram\DimmerSwitch;

use Hue\Contract\Program;
use Hue\Repository\SceneRepository;

final class TimeBasedWithDimmer extends AbstractDimmerSwitchProgram implements Program
{
    private $energize;
    private $concentrate;
    private $read;
    private $relax;
    private $nightlight;

    public function apply(): void
    {
        $scenes = (new SceneRepository($this->api))->getAll();
        $this->energize = $scenes->byNameAndGroup('Energize', $this->groupOrLight->id());
        $this->concentrate = $scenes->byNameAndGroup('Concentrate', $this->groupOrLight->id());
        $this->read = $scenes->byNameAndGroup('Read', $this->groupOrLight->id());
        $this->relax = $scenes->byNameAndGroup('Relax', $this->groupOrLight->id());
        $this->nightlight = $scenes->byNameAndGroup('Nightlight', $this->groupOrLight->id());

        // Create rules
        $this->createRulesForOnButton();
        $this->createRulesForUpButton();
        $this->createRulesForDownButton();
        $this->createRulesForOffButton();
    }

    private function createRulesForOnButton(): void
    {
        // 05:30 - 11:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press morning", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:30:00/T11:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->energize->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 11:00 - 17:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press day", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T11:00:00/T17:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 17:00 - 20:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press evening", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->read->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 20:00 - 23:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press late", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T00:00:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->relax->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 23:00 - 05:30
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press night", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T00:00:00/T05:30:00'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->nightlight->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Long press = bright
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1003'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForUpButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 8,
                'bri_inc' => 25
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-press, all off", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->relax->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForDownButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 8,
                'bri_inc' => -25
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-press, all off", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->nightlight->id()]],
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

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} off-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '4003'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => '/groups/0/action', 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}