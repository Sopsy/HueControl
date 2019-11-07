<?php
declare(strict_types=1);

namespace Hue\Program\DimmerSwitch;

use Hue\Contract\GroupInterface;
use Hue\Contract\Program;
use Hue\Contract\ResourceInterface;
use Hue\Repository\GroupRepository;
use Hue\Repository\RuleRepository;
use Hue\Repository\SceneRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;
use ReflectionClass;

final class SceneButtonsWithLongOff extends AbstractDimmerSwitchProgram implements Program
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

        $scenes = (new SceneRepository($this->api))->getAll();

        // Create rules
        $this->createRulesForOnButton($ruleRepo, $scenes, $switch, $group);
        $this->createRulesForUpButton($ruleRepo, $scenes, $switch, $group);
        $this->createRulesForDownButton($ruleRepo, $scenes, $switch, $group);
        $this->createRulesForOffButton($ruleRepo, $switch, $group);
    }

    private function createRulesForOnButton(RuleRepository $ruleRepo, GroupInterface $scenes, ResourceInterface $switch, ResourceInterface $group): void
    {
        $concentrate = $scenes->byNameAndGroup('Concentrate', $group->id());
        $rule = $ruleRepo->create("Switch {$switch->id()} on-press", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForUpButton(RuleRepository $ruleRepo, GroupInterface $scenes, ResourceInterface $switch, ResourceInterface $group): void
    {
        $relax = $scenes->byNameAndGroup('Relax', $group->id());
        $rule = $ruleRepo->create("Switch {$switch->id()} up-press", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '2000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForDownButton(RuleRepository $ruleRepo, GroupInterface $scenes, ResourceInterface $switch, ResourceInterface $group): void
    {
        $nightlight = $scenes->byNameAndGroup('Nightlight', $group->id());
        $rule = $ruleRepo->create("Switch {$switch->id()} down-press", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '3000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx']
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }

    private function createRulesForOffButton(RuleRepository $ruleRepo, ResourceInterface $switch, ResourceInterface $group): void
    {
        $rule = $ruleRepo->create("Switch {$switch->id()} off-press", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '4000'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        $rule = $ruleRepo->create("Switch {$switch->id()} off-long", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '4003'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
        ], [
            ['address' => '/groups/0/action', 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}