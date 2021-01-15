<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\ResourceLinksInterface;

final class ResourceLinks implements ResourceLinksInterface
{
    public function __construct(
        private int $id,
        private string $name,
        private int $classId,
        private array $links
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

    public function type(): string
    {
        return 'Link';
    }

    public function links(): array
    {
        return $this->links;
    }

    public function apiUrl(): string
    {
        return "/resourcelinks/{$this->id()}";
    }
}