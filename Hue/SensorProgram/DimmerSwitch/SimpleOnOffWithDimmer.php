<?php
declare(strict_types=1);

namespace Hue\SensorProgram\DimmerSwitch;

use Hue\Contract\Program;

final class SimpleOnOffWithDimmer extends AbstractDimmerSwitchProgram implements Program
{
    protected $supportsSingleLight = true;

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
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => ['on' => true]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => ['bri' => 255, 'ct' => 300]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForUpButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => 30,
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => 56,
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-rele", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2003'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => [
                'bri_inc' => 0,
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForDownButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => -30,
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => -56,
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-rele", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3003'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => [
                'bri_inc' => 0,
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
            ['address' => $this->groupOrLight->apiSetStateUrl(), 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}