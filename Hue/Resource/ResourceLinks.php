<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\ResourceInterface;

final class ResourceLinks implements ResourceInterface
{
    private $id;
    private $name;
    private $type;
    private $links;

    public function __construct(int $id, string $name, string $type, array $links)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->links = $links;
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

    public function links(): array
    {
        return $this->links;
    }
}