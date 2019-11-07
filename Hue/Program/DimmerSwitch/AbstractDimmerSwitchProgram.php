<?php
declare(strict_types=1);

namespace Hue\Program\DimmerSwitch;

use Hue\Contract\ApiInterface;
use Hue\Repository\ResourceLinkRepository;
use Hue\Repository\RuleRepository;
use Hue\Resource\Sensor;

abstract class AbstractDimmerSwitchProgram
{
    protected $api;
    protected $switchName;
    protected $groupName;

    public function __construct(ApiInterface $api, string $switchName, string $groupName)
    {
        $this->api = $api;
        $this->switchName = $switchName;
        $this->groupName = $groupName;
    }

    protected function removeOldRules(Sensor $switch): void
    {
        echo "Deleting old rules and resource links for '{$switch->name()}'...\n";

        $ruleRepo = new RuleRepository($this->api);
        $resourceLinkRepo = new ResourceLinkRepository($this->api);
        $rules = $ruleRepo->getAll();
        $resourceLinks = $resourceLinkRepo->getAll();

        // Remove resource links
        if ($resourceLinks->nameExists($this->switchName)) {
            $links = $resourceLinks->byName($this->switchName);
            $resourceLinkRepo->delete($links->id());
            echo "Deleted resource links: {$links->id()} ({$links->name()})\n";
        }

        // Remove old rules
        foreach ($rules->all() as $rule) {
            foreach ($rule->conditions() as $condition) {
                if (in_array($condition->address, ["/sensors/{$switch->id()}/state/lastupdated", "/sensors/{$switch->id()}/state/buttonevent"])) {
                    $ruleRepo->delete($rule->id());
                    echo "Deleted rule: {$rule->id()} ({$rule->name()})\n";
                }
            }
        }
    }
}