<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Contract\RequestHandlerInterface;
use Hue\Repository\GroupRepository;
use Hue\Repository\SceneRepository;

final class GetScenes implements RequestHandlerInterface
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(string ...$args): void
    {
        if (!empty($args[0])) {
            echo "Scenes in {$this->bridge->name()} for {$args[0]}:\n\n";

            foreach ((new GroupRepository($this->bridge->api()))->getAll()->byName($args[0])->scenes() AS $scene) {
                echo "Group {$scene->group()}: {$scene->id()} ({$scene->name()})\n";
            }
        } else {
            echo "Scenes in {$this->bridge->name()}:\n\n";

            foreach ((new SceneRepository($this->bridge->api()))->getAll()->all() AS $scene) {
                echo "Group {$scene->group()}: {$scene->id()} ({$scene->name()})\n";
            }
        }
    }
}