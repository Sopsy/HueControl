<?php
declare(strict_types=1);

namespace Hue\SensorProgram;

use Hue\Contract\ApiInterface;
use Hue\Repository\GroupRepository;
use Hue\Repository\LightRepository;
use Hue\Repository\ResourceLinkRepository;
use Hue\Repository\RuleRepository;
use Hue\Repository\SensorRepository;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use function in_array;

abstract class AbstractSensorProgram
{
    protected $api;
    protected $sensor;
    protected $groupOrLight;
    protected $ruleRepo;
    protected $sensorRepo;
    protected $singleLight;
    protected $supportsSingleLight = false;

    /**
     * AbstractSensorProgram constructor.
     * @param ApiInterface $api
     * @param string $sensorName
     * @param string $groupName
     * @throws ReflectionException
     */
    public function __construct(ApiInterface $api, string $sensorName, string $groupName)
    {
        $this->api = $api;
        $this->ruleRepo = new RuleRepository($this->api);
        $this->sensorRepo = new SensorRepository($this->api);

        $groups = (new GroupRepository($this->api))->getAll();
        $sensors = $this->sensorRepo->getAll();

        if (!$groups->nameExists($groupName)) {
            // Maybe it is a single light?
            $lights = (new LightRepository($this->api))->getAll();
            if (!$lights->nameExists($groupName)) {
                throw new InvalidArgumentException("Group or light '{$groupName}' does not exist");
            }
            $this->groupOrLight = $lights->byName($groupName);
            $this->singleLight = true;
        } else {
            $this->groupOrLight = $groups->byName($groupName);
            $this->singleLight = false;
        }

        if (!$sensors->nameExists($sensorName)) {
            throw new InvalidArgumentException("Sensor '{$sensorName}' does not exist");
        }
        $this->sensor = $sensors->byName($sensorName);

        if (!$this->sensorIsCompatible()) {
            throw new InvalidArgumentException('Incompatible sensor type for selected program');
        }

         if ($this->singleLight && !$this->supportsSingleLight) {
             throw new InvalidArgumentException('This program does not support single lights');
         }

        echo 'Installing program ' . (new ReflectionClass($this))->getShortName() . " to '{$sensorName}' to control group or light '{$groupName}'...\n";

        $this->removeOldRules();

        $this->apply();
    }

    protected function removeOldRules(): void
    {
        echo "Deleting old rules and resource links for '{$this->sensor->name()}'...\n";

        $ruleRepo = new RuleRepository($this->api);
        $resourceLinkRepo = new ResourceLinkRepository($this->api);

        // Remove resource links
        $resourceLinks = $resourceLinkRepo->getAll();
        if ($resourceLinks->nameExists($this->sensor->name())) {
            $links = $resourceLinks->byName($this->sensor->name());
            $resourceLinkRepo->delete($links->id());
            echo "Deleted resource links: {$links->id()} ({$links->name()})\n";
        }

        // Remove old rules
        foreach ($ruleRepo->getAll()->all() as $rule) {
            foreach ($rule->conditions() as $condition) {
                if (in_array($condition->address, [
                        "/sensors/{$this->sensor->id()}/state/lastupdated",
                        "/sensors/{$this->sensor->id()}/state/buttonevent",
                        "/sensors/{$this->sensor->id()}/state/presence",
                    ])) {
                    $ruleRepo->delete($rule->id());
                    echo "Deleted rule: {$rule->id()} ({$rule->name()})\n";

                    continue 2;
                }
            }
        }
    }

    abstract protected function sensorIsCompatible(): bool;

    abstract protected function apply(): void;
}