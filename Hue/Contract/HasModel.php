<?php
declare(strict_types=1);

namespace Hue\Contract;

interface HasModel
{
    public function model(): string;
}