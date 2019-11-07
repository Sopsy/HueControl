<?php
declare(strict_types=1);

namespace Hue\Group;

use Hue\Contract\GroupInterface;
use Hue\Contract\ResourceInterface;
use Hue\Resource\Rule;
use Hue\Resource\Scene;
use RuntimeException;
use function var_dump;

final class RuleGroup implements GroupInterface
{
    private $items;

    public function __construct(Rule ...$scenes)
    {
        $this->items = $scenes;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function byName($name): ResourceInterface
    {
        foreach ($this->items as $item) {
            if ($item->name() === $name) {
                return $item;
            }
        }

        throw new RuntimeException("Rule '{$name}' not found.");
    }

    public function byId($id): ResourceInterface
    {
        $id = (int)$id;

        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        throw new RuntimeException("Rule ID '{$id}' not found.");
    }
}