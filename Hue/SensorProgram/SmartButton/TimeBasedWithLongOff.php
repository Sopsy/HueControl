<?php
declare(strict_types=1);

namespace Hue\SensorProgram\SmartButton;

use Hue\Contract\Program;
use Hue\Repository\SceneRepository;

final class TimeBasedWithLongOff extends AbstractSmartButtonProgram implements Program
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
        $this->createRulesForShortPress();
        $this->createRulesForLongPress();
    }

    private function createRulesForShortPress(): void
    {

        // At least one light on = turn lights off
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} short-rele, lights on", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 05:30 - 11:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} short-rele morning", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:30:00/T11:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->energize->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 11:00 - 17:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} short-rele day", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T11:00:00/T17:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 17:00 - 20:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} short-rele evening", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->read->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 20:00 - 23:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} short-rele late", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T00:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->relax->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 23:00 - 05:30
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} short-rele night", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T00:00:00/T05:30:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->nightlight->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
    
    private function createRulesForLongPress(): void
    {
        // Long press when lights are on = all off
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} long-press, any on", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
        ], [
            ['address' => '/groups/0/action', 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Long press when lights are off = bright
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} long-press, all off", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $this->concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}