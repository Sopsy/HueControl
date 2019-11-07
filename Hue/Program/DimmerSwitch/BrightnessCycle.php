<?php
declare(strict_types=1);

namespace Hue\Program\DimmerSwitch;

use Hue\Contract\Program;
use Hue\Repository\GroupRepository;
use Hue\Repository\ResourceLinkRepository;
use Hue\Repository\RuleRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;

class BrightnessCycle extends AbstractDimmerSwitchProgram implements Program
{
    public function apply(): void
    {
        $groupRepo = new GroupRepository($this->api);
        $ruleRepo = new RuleRepository($this->api);
        $sensorRepo = new SensorRepository($this->api);
        $resourceLinkRepo = new ResourceLinkRepository($this->api);

        $groups = $groupRepo->getAll();
        $rules = $ruleRepo->getAll();
        $sensors = $sensorRepo->getAll();
        $resourceLinks = $resourceLinkRepo->getAll();

        if (!$groups->nameExists($this->groupName)) {
            throw new InvalidArgumentException("Group '{$this->groupName}' does not exist");
        }
        $group = $groups->byName($this->groupName);

        if (!$sensors->nameExists($this->switchName)) {
            throw new InvalidArgumentException("Sensor '{$this->switchName}' does not exist");
        }
        $switch = $sensors->byName($this->switchName);

        $return = '';

        // Remove old rules
        if ($resourceLinks->nameExists($this->switchName)) {
            $links = $resourceLinks->byName($this->switchName);
            foreach ($links->linksByType('rules') as $link) {
                $rule = $rules->byId($link);
                $ruleRepo->delete($rule->id());
                $return .= "Deleted rule for '{$this->switchName}': {$rule->id()} ({$rule->name()})\n";
            }

            // Remove resource links
            $resourceLinkRepo->delete($links->id());
            $return .= "Deleted resource links: {$links->id()} ({$links->name()})\n";
        }


        $flag = $sensorRepo->createStatus("Switch {$switch->id()} status");
        $return .= "Created new memory status sensor: {$flag->id()} ({$flag->name()})\n";

    }
}