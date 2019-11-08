<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Contract\RequestHandlerInterface;
use Hue\Repository\SceneRepository;

final class DeleteScene implements RequestHandlerInterface
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(string ...$args): void
    {
        $sceneRepo = new SceneRepository($this->bridge->api());
        $scene = $sceneRepo->getAll()->byId($args[0]);

        $sceneRepo->delete($scene->id());
    }
}