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

    public function nameExists($name): bool
    {
        foreach ($this->items AS $item) {
            if ($item->name() === $name) {
                return true;
            }
        }

        return false;
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

    public function idExists($id): bool
    {
        foreach ($this->items AS $item) {
            if ($item->id() === $id) {
                return true;
            }
        }

        return false;
    }

    public function byId($id): ResourceInterface
    {
        $id = (int)$id;

        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        throw new RuntimeException("Resource link ID '{$id}' not found.");
    }
}