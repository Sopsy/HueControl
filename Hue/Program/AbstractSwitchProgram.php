<?php
declare(strict_types=1);

namespace Hue\Program;

use Hue\Contract\ApiInterface;
use Hue\Repository\ResourceLinkRepository;
use Hue\Repository\RuleRepository;
use Hue\Resource\Sensor;
use InvalidArgumentException;
use function in_array;

abstract class AbstractSwitchProgram
{
    protected $api;
    protected $switchName;
    protected $groupName;

    public function __construct(ApiInterface $api, string $switchName, string $groupName)
    {
        $this->api = $api;
        $this->switchName = $switchName;
        $this->groupName = $groupName;

        if (!$this->switchIsCompatible()) {
            throw new InvalidArgumentException('Incompatible switch type for selected program');
        }
    }

    protected function removeOldRules(Sensor $switch): void
    {
        echo "Deleting old rules and resource links for '{$switch->name()}'...\n";

        $ruleRepo = new RuleRepository($this->api);
        $resourceLinkRepo = new ResourceLinkRepository($this->api);

        // Remove resource links
        $resourceLinks = $resourceLinkRepo->getAll();
        if ($resourceLinks->nameExists($this->switchName)) {
            $links = $resourceLinks->byName($this->switchName);
            $resourceLinkRepo->delete($links->id());
            echo "Deleted resource links: {$links->id()} ({$links->name()})\n";
        }

        // Remove old rules
        foreach ($ruleRepo->getAll()->all() as $rule) {
            foreach ($rule->conditions() as $condition) {
                if (in_array($condition->address, ["/sensors/{$switch->id()}/state/lastupdated", "/sensors/{$switch->id()}/state/buttonevent"])) {
                    $ruleRepo->delete($rule->id());
                    echo "Deleted rule: {$rule->id()} ({$rule->name()})\n";

                    continue 2;
                }
            }
        }
    }

    abstract protected function switchIsCompatible(): bool;
}