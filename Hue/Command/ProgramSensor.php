<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Contract\ProgramInterface;
use Hue\Contract\SensorInterface;
use Hue\Repository\GroupRepository;
use Hue\Repository\ProgramRepository;
use Hue\Repository\ResourceLinksRepository;
use Hue\Repository\RuleRepository;
use Hue\Repository\SensorRepository;
use Hue\SensorProgram\ZLLSwitch;
use Hue\SensorProgram\SmartButton;
use Hue\SensorProgram\ZLLPresence;
use InvalidArgumentException;
use function array_key_exists;
use function class_exists;
use function fgets;
use function in_array;
use const STDIN;

final class ProgramSensor implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        $sensorRepo = new SensorRepository($this->bridge->api());
        $groupRepo = new GroupRepository($this->bridge->api());
        $programRepo = new ProgramRepository($this->bridge->api());

        $supportedTypes = [
            SensorRepository::TYPE_SWITCH,
            SensorRepository::TYPE_PRESENCE,
            SensorRepository::TYPE_TAP_SWITCH,
        ];

        // Selecting a sensor
        $sensors = $sensorRepo->all();
        if (isset($args[0])) {
            $selectedSensorId = (int)$args[0];
        } else {
            echo "Choose a sensor (type a number and press enter):\n";
            foreach ($sensors as $sensor) {
                if (!in_array($sensor->type(), $supportedTypes)) {
                    continue;
                }

                echo "{$sensor->id()}: {$sensor->name()} ({$sensor->type()}: {$sensor->model()})\n";
            }
            $selectedSensorId = (int)fgets(STDIN);
        }
        if (!array_key_exists($selectedSensorId, $sensors)) {
            throw new InvalidArgumentException('Invalid sensor!');
        }
        $sensor = $sensors[$selectedSensorId];
        echo "Sensor {$sensor->name()} selected.\n";

        // Selecting a group
        $groups = $groupRepo->all();
        if (isset($args[1])) {
            $selectedGroupId = (int)$args[1];
        } else {
            echo "Choose a group (room) to control (type a number and press enter):\n";
            foreach ($groups as $group) {
                echo "{$group->id()}: {$group->name()}\n";
            }
            $selectedGroupId = (int)fgets(STDIN);
        }
        if (!array_key_exists($selectedGroupId, $groups)) {
            throw new InvalidArgumentException('Invalid group!');
        }
        $group = $groups[$selectedGroupId];
        echo "Group {$group->name()} selected.\n";

        // Selecting a program
        $programs = $programRepo->all($sensor->type());
        if (isset($args[2])) {
            $selectedProgramId = (int)$args[2];
        } else {
            echo "Choose a program configure (type a number and press enter):\n";
            foreach ($programs as $programId => $program) {
                echo "{$programId}: {$program->name()}\n";
                echo "  {$program->description()}\n";
            }
            $selectedProgramId = (int)fgets(STDIN);
        }
        if (!array_key_exists($selectedProgramId, $programs)) {
            throw new InvalidArgumentException('Invalid program!');
        }
        $program = $programs[$selectedProgramId];
        echo "Program {$program->name()} selected.\n";

        // Configure
        $program = $program->withGroupAndSensor($group, $sensor);
        $rules = $program->rules();
        $resourceLinks = $program->resourceLinks();

        $this->deleteOldRules($sensor);
        $ruleRepo = new RuleRepository($this->bridge->api());
        foreach ($rules as $rule) {
            $resourceLinks[] = $ruleRepo->create($rule->name(), $rule->conditions(), $rule->actions());
            echo "Created rule {$rule->name()}\n";
        }

        $resourceLinksRepo = new ResourceLinksRepository($this->bridge->api());
        $newResourceLinks = $resourceLinksRepo->create("{$sensor->name()}", 'Sensor program', 1, $resourceLinks);
        echo "Resource links created: {$newResourceLinks->id()}\n";
        foreach ($resourceLinks as $link) {
            echo "With resource link {$link->name()}: {$link->apiUrl()}\n";
        }

        echo "Deleting unused generic sensors...\n";
        (new SensorRepository($this->bridge->api()))->deleteUnusedGeneric();

        echo "Programming done!\n";
    }

    private function deleteOldRules(SensorInterface $sensor): void
    {
        echo "Deleting old rules and resource links for '{$sensor->name()}'...\n";

        $ruleRepo = new RuleRepository($this->bridge->api());
        $resourceLinkRepo = new ResourceLinksRepository($this->bridge->api());

        // Remove resource links
        foreach ($resourceLinkRepo->all() as $resourceLinks) {
            if ($resourceLinks->name() === $sensor->name()) {
                $resourceLinkRepo->delete($resourceLinks->id());
                echo "Deleted resource links: {$resourceLinks->id()} ({$resourceLinks->name()})\n";
            }
        }

        // Remove old rules
        foreach ($ruleRepo->all() as $rule) {
            foreach ($rule->conditions() as $condition) {
                if (in_array($condition->address, [
                    "/sensors/{$sensor->id()}/state/lastupdated",
                    "/sensors/{$sensor->id()}/state/buttonevent",
                    "/sensors/{$sensor->id()}/state/presence",
                ])) {
                    $ruleRepo->delete($rule->id());
                    echo "Deleted rule: {$rule->id()} ({$rule->name()})\n";

                    continue 2;
                }
            }
        }
    }
}