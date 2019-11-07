<?php

namespace Hue\Contract;

interface Program
{
    public function output(): string;

    public function apply(): void;
}