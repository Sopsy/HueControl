<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\GroupRepository;
use Hue\Repository\SceneRepository;

final class GetScenes implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        if (!empty($args[0])) {
            echo "Scenes in {$this->bridge->name()} for {$args[0]}:\n\n";

            foreach ((new GroupRepository($this->bridge->api()))->byId((int)$args[0])->scenes() AS $scene) {
                echo "Group {$scene->group()}: {$scene->id()} ({$scene->name()})\n";
            }
        } else {
            echo "Scenes in {$this->bridge->name()}:\n\n";

            foreach ((new SceneRepository($this->bridge->api()))->all() AS $scene) {
                echo "Group {$scene->group()}: {$scene->id()} ({$scene->name()})\n";
            }
        }
    }
}