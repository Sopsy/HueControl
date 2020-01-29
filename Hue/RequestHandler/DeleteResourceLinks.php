<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Contract\RequestHandlerInterface;
use Hue\Repository\ResourceLinksRepository;

final class DeleteResourceLinks implements RequestHandlerInterface
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(string ...$args): void
    {
        $resourceLinksRepo = new ResourceLinksRepository($this->bridge->api());
        $resourceLinks = $resourceLinksRepo->getAll()->byId($args[0]);

        $resourceLinksRepo->delete($resourceLinks->id());
    }
}