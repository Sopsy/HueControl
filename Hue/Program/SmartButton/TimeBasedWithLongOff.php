<?php
declare(strict_types=1);

namespace Hue\Program\SmartButton;

use Hue\Contract\Program;
use Hue\Contract\ResourceInterface;
use Hue\Repository\GroupRepository;
use Hue\Repository\RuleRepository;
use Hue\Repository\SceneRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;
use ReflectionClass;

final class TimeBasedWithLongOff extends AbstractSmartButtonProgram implements Program
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

        // Create rules
        $this->createRules($ruleRepo, $switch, $group);
    }

    private function createRules(RuleRepository $ruleRepo, ResourceInterface $switch, ResourceInterface $group): void
    {
        $scenes = (new SceneRepository($this->api))->getAll();
        $energize = $scenes->byNameAndGroup('Energize', $group->id());
        $concentrate = $scenes->byNameAndGroup('Concentrate', $group->id());
        $read = $scenes->byNameAndGroup('Read', $group->id());
        $relax = $scenes->byNameAndGroup('Relax', $group->id());
        $nightlight = $scenes->byNameAndGroup('Nightlight', $group->id());

        // At least one light on = turn lights off
        $rule = $ruleRepo->create("Switch {$switch->id()} short-rele, lights on", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 05:30 - 11:00
        $rule = $ruleRepo->create("Switch {$switch->id()} short-rele morning", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:30:00/T11:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 11:00 - 17:00
        $rule = $ruleRepo->create("Switch {$switch->id()} short-rele day", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T11:00:00/T17:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 17:00 - 20:00
        $rule = $ruleRepo->create("Switch {$switch->id()} short-rele evening", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 20:00 - 23:00
        $rule = $ruleRepo->create("Switch {$switch->id()} short-rele late", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T00:00:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // 23:00 - 05:30
        $rule = $ruleRepo->create("Switch {$switch->id()} short-rele night", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1002'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T00:00:00/T05:30:00'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Long press when lights are on = all off
        $rule = $ruleRepo->create("Switch {$switch->id()} long-press, lights on", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1001'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'true'],
        ], [
            ['address' => '/groups/0/action', 'method' => 'PUT', 'body' => ['on' => false]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";

        // Long press when lights are off = bright
        $rule = $ruleRepo->create("Switch {$switch->id()} long-press, lights on", [
            ['address' => "/sensors/{$switch->id()}/state/buttonevent", 'operator' => 'eq', 'value' => '1001'],
            ['address' => "/sensors/{$switch->id()}/state/lastupdated", 'operator' => 'dx'],
            ['address' => "/groups/{$group->id()}/state/any_on", 'operator' => 'eq', 'value' => 'false'],
        ], [
            ['address' => "/groups/{$group->id()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
        ]);
        echo "Created new rule: {$rule->id()} ({$rule->name()})\n";
    }
}