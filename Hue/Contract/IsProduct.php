<?php
declare(strict_types=1);

namespace Hue\Contract;

interface IsProduct
{
    public function manufacturer(): string;
    public function productName(): string;
}