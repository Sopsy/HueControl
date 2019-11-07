<?php
declare(strict_types=1);

namespace Hue\Contract;

interface GroupInterface
{
    public function idExists($id): bool;

    public function byId($id): ResourceInterface;

    public function nameExists($name): bool;

    public function byName($name): ResourceInterface;

    /**
     * @return ResourceInterface[]
     */
    public function all(): array;
}