<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\ResourceInterface;

final class Light implements ResourceInterface
{
    private $id;
    private $name;
    private $type;

    public function __construct(int $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }
}