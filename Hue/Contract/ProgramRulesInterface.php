<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ProgramRulesInterface
{
    /**
     * @return RuleInterface[]
     */
    public function rules(): array;
}