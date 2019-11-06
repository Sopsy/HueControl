<?php
declare(strict_types=1);

namespace Hue;

use ArrayAccess;
use InvalidArgumentException;

abstract class AbstractGroup implements ArrayAccess
{
    protected $items;

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): Light
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (!($value instanceof Light)) {
            throw new InvalidArgumentException('Value is not an instance of Light');
        }

        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        unset($this->items[$offset]);
    }
}