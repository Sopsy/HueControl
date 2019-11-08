<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Bridge;
use Hue\Contract\RequestHandlerInterface;
use Hue\Repository\ResourceLinkRepository;

final class GetResourceLinks implements RequestHandlerInterface
{
    private $bridge;

    public function __construct(Bridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function handle(string ...$args): void
    {
        echo "Resource links in {$this->bridge->name()}:\n\n";

        foreach ((new ResourceLinkRepository($this->bridge->api()))->getAll()->all() AS $resourceLink) {
            echo "{$resourceLink->id()}: {$resourceLink->name()}\n";
            foreach ($resourceLink->links() as $link) {
                echo "  - {$link}\n";
            }
            echo "\n";
        }
    }
}