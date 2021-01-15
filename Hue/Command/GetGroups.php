<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\GroupRepository;

final class GetGroups implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        echo "Groups in {$this->bridge->name()}:\n\n";

        foreach ((new GroupRepository($this->bridge->api()))->all() AS $group) {
            echo "{$group->id()}: {$group->name()}\n";
            echo "  Lights:\n";
            foreach ($group->lights() as $light) {
                echo "    {$light->id()}: {$light->name()}\n";
            }
            echo "  Scenes:\n";
            foreach ($group->scenes() as $scene) {
                echo "    {$scene->id()}: {$scene->name()}\n";
            }
        }
    }
}