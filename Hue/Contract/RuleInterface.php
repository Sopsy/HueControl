<?php
declare(strict_types=1);

namespace Hue\Contract;

interface RuleInterface extends ResourceInterface
{
    public function id(): int;

    public function conditions(): array;

    public function actions(): array;
}