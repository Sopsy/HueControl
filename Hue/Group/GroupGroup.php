<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Contract\GroupInterface;
use Hue\Contract\ResourceInterface;
use Hue\Resource\Group;
use RuntimeException;

// Group of groups (rooms)
final class GroupGroup implements GroupInterface
{
    private $items;

    public function __construct(Group ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return Group[]
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

        throw new RuntimeException("Group '{$name}' not found.");
    }
}