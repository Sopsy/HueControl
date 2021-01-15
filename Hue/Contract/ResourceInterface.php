<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ResourceInterface
{
    // Scene IDs are a string, others int
    public function id(): int|string;

    public function name(): string;

    public function apiUrl(): string;
}