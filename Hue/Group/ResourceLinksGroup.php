<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Resource\ResourceLinks;
use RuntimeException;

final class ResourceLinksGroup
{
    private $items;

    public function __construct(ResourceLinks ...$items)
    {
        $this->items = $items;
    }

    public function resourceLinks(): array
    {
        return $this->items;
    }

    public function resourceLinksByName(string $name): ResourceLinks
    {
        foreach ($this->items AS $item) {
            if ($item->name() === $name) {
                return $item;
            }
        }

        throw new RuntimeException("Resource links for '{$name}' not found.");
    }
}