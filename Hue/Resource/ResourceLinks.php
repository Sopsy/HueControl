<?php
declare(strict_types=1);

namespace Hue\Resource;

final class ResourceLinks
{
    private $id;
    private $name;
    private $links;

    public function __construct(int $id, string $name, array $links)
    {
        $this->id = $id;
        $this->name = $name;
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

    public function links(): array
    {
        return $this->links;
    }
}