<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\SceneRepository;

final class DeleteScene implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        $sceneRepo = new SceneRepository($this->bridge->api());
        $scene = $sceneRepo->byId($args[0]);

        $sceneRepo->delete($scene->id());
    }
}