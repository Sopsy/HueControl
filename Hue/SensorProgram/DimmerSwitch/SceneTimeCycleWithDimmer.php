<?php
declare(strict_types=1);

namespace Hue\SensorProgram\DimmerSwitch;

use Hue\Contract\Program;
use Hue\Repository\SceneRepository;

final class SceneTimeCycleWithDimmer extends AbstractDimmerSwitchProgram implements Program
{
    private $statusSensor;

    public function apply(): void
    {
        // Create status flag for repeated presses
        $this->statusSensor = $this->sensorRepo->createStatus("Switch {$this->sensor->id()} status");
        echo "Created new memory status sensor: {$this->statusSensor->id()} ({$this->statusSensor->name()})\n";

        $this->addResourceLink($this->statusSensor);

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

        $this->addResourceLink($energize);
        $this->addResourceLink($concentrate);
        $this->addResourceLink($read);
        $this->addResourceLink($relax);
        $this->addResourceLink($nightlight);

        // 05:00 - 10:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press morning", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:00:00/T10:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 1]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 10:00 - 17:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press day", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T10:00:00/T17:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 2]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 17:00 - 20:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press evening", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 3]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 20:00 - 23:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press late", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T23:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 4]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 23:00 - 05:00
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press night", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T23:00:00/T05:00:00'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 5]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 1->2
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press 1", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '1'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 2]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 2->3
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press 2", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '2'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 3]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 3->4
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press 3", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '3'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 4]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 4->5
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press 4", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '4'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 5]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 5->1
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} on-press 5", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$this->groupOrLight->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$this->statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '5'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 1]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForUpButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => 30
            ]]
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => 56
            ]]
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} up-rele", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2003'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'bri_inc' => 0
            ]]
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForDownButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => -30
            ]]
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-long", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3001'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => -56
            ]]
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} down-rele", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3003'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => [
                'bri_inc' => 0
            ]]
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForOffButton(): void
    {
        $rule = $this->ruleRepo->create("Switch {$this->sensor->id()} off-press", [
            ['address' => "/sensors/{$this->sensor->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '4000'],
            ['address' => "/sensors/{$this->sensor->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$this->groupOrLight->id()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
            ['address' => "/sensors/{$this->statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 0]],
        ]);

        $this->addResourceLink($rule);

        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}