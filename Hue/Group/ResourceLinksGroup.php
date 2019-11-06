<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Contract\GroupInterface;
use Hue\Contract\ResourceInterface;
use Hue\Resource\ResourceLinks;
use RuntimeException;

final class ResourceLinksGroup implements GroupInterface
{
    private $items;

    public function __construct(ResourceLinks ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return ResourceInterface[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function byName($name): ResourceInterface
    {
        foreach ($this->items AS $item) {
            if ($item->name() === $name) {
                return $item;
            }
        }

        throw new RuntimeException("Resource links for '{$name}' not found.");
    }
}