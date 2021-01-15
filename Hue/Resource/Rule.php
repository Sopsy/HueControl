<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\RuleInterface;

final class Rule implements RuleInterface
{
    public function __construct(
        private int $id,
        private string $name,
        private array $conditions,
        private array $actions
    )
    {
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