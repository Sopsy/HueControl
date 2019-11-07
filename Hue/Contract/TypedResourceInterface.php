<?php
declare(strict_types=1);

namespace Hue\Contract;

interface TypedResourceInterface extends ResourceInterface
{
    public function type(): string;
}