<?php
declare(strict_types=1);

namespace Hue\Contract;

interface SceneInterface extends TypedResourceInterface
{
    public function id(): string;

    public function group(): int;
}