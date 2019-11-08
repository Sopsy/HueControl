<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\ResourceInterface;

final class Rule implements ResourceInterface
{
    private $id;
    private $name;
    private $conditions;
    private $actions;

    public function __construct(int $id, string $name, array $conditions, array $actions)
    {
        $this->id = $id;
        $this->name = $name;
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function conditions(): array
    {
        return $this->conditions;
    }

    public function actions(): array
    {
        return $this->actions;
    }

    public function apiUrl(): string
    {
        return "/rules/{$this->id()}";
    }
}