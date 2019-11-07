<?php
declare(strict_types=1);

namespace Hue\Contract;

use stdClass;

interface ApiResponseInterface
{
    public function success(): bool;

    public function message(): string;

    public function data(): stdClass;
}