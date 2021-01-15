<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Bridge;
use Hue\Contract\CommandInterface;
use Hue\Repository\ResourceLinksRepository;

final class DeleteResourceLinks implements CommandInterface
{
    public function __construct(private Bridge $bridge)
    {
    }

    public function run(string ...$args): void
    {
        $resourceLinksRepo = new ResourceLinksRepository($this->bridge->api());
        $resourceLinks = $resourceLinksRepo->byId((int)$args[0]);

        $resourceLinksRepo->delete($resourceLinks->id());
    }
}