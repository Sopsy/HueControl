<?php
declare(strict_types=1);

namespace Hue\Contract;

interface GroupInterface
{
    /**
     * @return ResourceInterface[]
     */
    public function all(): array;

    public function byName($name): ResourceInterface;

    public function byId($id): ResourceInterface;
}