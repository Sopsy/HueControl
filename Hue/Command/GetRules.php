<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\RuleRepository;
use function json_encode;

final class GetRules implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        echo "Rules in {$this->bridge->name()}:\n\n";

        foreach ((new RuleRepository($this->bridge->api()))->all() AS $rule) {
            echo "{$rule->id()}: {$rule->name()}:\n";
            echo "  Conditions:\n";
            foreach ($rule->conditions() as $condition) {
                $condition = json_encode($condition, JSON_THROW_ON_ERROR);
                echo "    - {$condition}\n";
            }
            echo "  Actions:\n";
            foreach ($rule->actions() as $action) {
                $action = json_encode($action, JSON_THROW_ON_ERROR);
                echo "    - {$action}\n";
            }
        }
    }
}