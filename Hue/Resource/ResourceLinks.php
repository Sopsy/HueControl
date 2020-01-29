<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\TypedResourceInterface;
use function str_replace;
use function strpos;

final class ResourceLinks implements TypedResourceInterface
{
    private $id;
    private $name;
    private $classId;
    private $links;

    public function __construct(int $id, string $name, int $classId, array $links)
    {
        $this->id = $id;
        $this->name = $name;
        $this->classId = $classId;
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
        return 'Link';
    }

    public function classId(): int
    {
        return $this->classId;
    }

    public function links(): array
    {
        return $this->links;
    }

    public function linksByType(string $type): array
    {
        $return = [];
        foreach ($this->links() as $link) {
            if (strpos($link, '/' . $type . '/') !== 0) {
                continue;
            }

            $return[] = str_replace('/' . $type . '/', '', $link);
        }

        return $return;
    }

    public function apiUrl(): string
    {
        return "/resourcelinks/{$this->id()}";
    }
}