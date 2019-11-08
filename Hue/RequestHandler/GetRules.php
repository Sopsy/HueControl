<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Contract\RequestHandlerInterface;
use Hue\Repository\RuleRepository;
use function json_encode;

final class GetRules implements RequestHandlerInterface
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(string ...$args): void
    {
        echo "Rules in {$this->bridge->name()}:\n\n";

        foreach ((new RuleRepository($this->bridge->api()))->getAll()->all() AS $rule) {
            echo "{$rule->id()}: {$rule->name()}:\n";
            echo "  Conditions:\n";
            foreach ($rule->conditions() as $condition) {
                $condition = json_encode($condition);
                echo "    - {$condition}\n";
            }
            echo "  Actions:\n";
            foreach ($rule->actions() as $action) {
                $action = json_encode($action);
                echo "    - {$action}\n";
            }
        }
    }
}