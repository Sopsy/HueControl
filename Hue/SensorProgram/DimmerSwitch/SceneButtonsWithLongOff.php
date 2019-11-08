<?php
declare(strict_types=1);

namespace Hue\SensorProgram\DimmerSwitch;

use Hue\Contract\Program;
use Hue\Repository\SceneRepository;

final class SceneButtonsWithLongOff extends AbstractDimmerSwitchProgram implements Program
{
    private $scenes;
    
    public function apply(): void
    {
        $this->scenes = (new SceneRepository($this->api))->getAll();

        // Create rules
        $this->createRulesForOnButton();
        $this->createRulesForUpButton();
        $this->createRulesForDownButton();
        $this->createRulesForOffButton();
    }

    private function createRulesForOnButton(): void
    {
        $concentrate = $this->scenes->byNameAndGroup('Concentrate', $this->groupOrLight->id());
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForUpButton(): void
    {
        $relax = $this->scenes->byNameAndGroup('Relax', $this->groupOrLight->id());
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForDownButton(): void
    {
        $nightlight = $this->scenes->byNameAndGroup('Nightlight', $this->groupOrLight->id());
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
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