<?php
declare(strict_types=1);

namespace Hue\Program\DimmerSwitch;

use Hue\Contract\Program;
use Hue\Contract\ResourceInterface;
use Hue\Repository\GroupRepository;
use Hue\Repository\RuleRepository;
use Hue\Repository\SceneRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;
use ReflectionClass;

final class BrightnessCycle extends AbstractDimmerSwitchProgram implements Program
{
    public function apply(): void
    {
        $groupRepo = new GroupRepository($this->api);
        $ruleRepo = new RuleRepository($this->api);
        $sensorRepo = new SensorRepository($this->api);

        $groups = $groupRepo->getAll();
        $sensors = $sensorRepo->getAll();

        if (!$groups->nameExists($this->groupName)) {
            throw new InvalidArgumentException("Group '{$this->groupName}' does not exist");
        }
        $group = $groups->byName($this->groupName);

        if (!$sensors->nameExists($this->switchName)) {
            throw new InvalidArgumentException("Sensor '{$this->switchName}' does not exist");
        }
        $switch = $sensors->byName($this->switchName);

        echo 'Installing program ' . (new ReflectionClass($this))->getShortName() . " to '{$this->switchName}' to control group '{$this->groupName}'...\n";

        $this->removeOldRules($switch);

        // Create status flag for repeated presses
        $statusSensor = $sensorRepo->createStatus("Switch {$switch->id()} status");
        echo "Created new memory status sensor: {$statusSensor->id()} ({$statusSensor->name()})\n";

        // Create rules
        $this->createRulesForOnButton($ruleRepo, $statusSensor, $switch, $group);
        $this->createRulesForUpButton($ruleRepo, $switch, $group);
        $this->createRulesForDownButton($ruleRepo, $switch, $group);
        $this->createRulesForOffButton($ruleRepo, $statusSensor, $switch, $group);
    }

    private function createRulesForOnButton(RuleRepository $ruleRepo, ResourceInterface $statusSensor, ResourceInterface $switch, ResourceInterface $group): void
    {
        $scenes = (new SceneRepository($this->api))->getAll();
        $energize = $scenes->byNameAndGroup('Energize', $group->id());
        $concentrate = $scenes->byNameAndGroup('Concentrate', $group->id());
        $read = $scenes->byNameAndGroup('Read', $group->id());
        $relax = $scenes->byNameAndGroup('Relax', $group->id());
        $nightlight = $scenes->byNameAndGroup('Nightlight', $group->id());

        // 05:00 - 10:00
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press morning", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:00:00/T10:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 1]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 10:00 - 17:00
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press day", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T10:00:00/T17:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 2]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 17:00 - 21:00
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press evening", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 3]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 21:00 - 23:00
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press late", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T21:00:00/T23:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 4]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 23:00 - 05:00
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press night", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T23:00:00/T05:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 5]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 1->2
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press 1", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '1'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 2]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 2->3
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press 2", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '2'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 3]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 3->4
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press 3", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '3'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 4]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 4->5
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press 4", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '4'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 5]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Status 5->1
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press 5", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
            ['address' => "/sensors/{$statusSensor->id()}/state/status", 'operator' => 'eq', 'value' => '5'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 1]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForUpButton(RuleRepository $ruleRepo, ResourceInterface $switch, ResourceInterface $group): void
    {
        $rule = $ruleRepo->create("Switch {$switch->id()} up-press", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => 30
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $ruleRepo->create("Switch {$switch->id()} up-long", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2001'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => 56
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $ruleRepo->create("Switch {$switch->id()} up-rele", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2003'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => [
                'bri_inc' => 0
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForDownButton(RuleRepository $ruleRepo, ResourceInterface $switch, ResourceInterface $group): void
    {
        $rule = $ruleRepo->create("Switch {$switch->id()} down-press", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => -30
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $ruleRepo->create("Switch {$switch->id()} down-long", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3001'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => [
                'transitiontime' => 9,
                'bri_inc' => -56
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $ruleRepo->create("Switch {$switch->id()} down-rele", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3003'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => [
                'bri_inc' => 0
            ]]
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForOffButton(RuleRepository $ruleRepo, ResourceInterface $statusSensor, ResourceInterface $switch, ResourceInterface $group): void
    {
        $rule = $ruleRepo->create("Switch {$switch->id()} off-press", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '4000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
            ['address' => "/sensors/{$statusSensor->id()}/state", 'method' => 'PUT', 'body' => ['status' => 0]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}