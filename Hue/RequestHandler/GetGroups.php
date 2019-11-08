<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Repository\GroupRepository;

final class GetGroups
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(...$args): void
    {
        echo "Groups in {$this->bridge->name()}:\n\n";

        foreach ((new GroupRepository($this->bridge->api()))->getAll()->all() AS $group) {
            echo "{$group->id()}: {$group->name()}\n";
        }
    }
}