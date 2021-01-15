<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ProgramInterface extends ProgramRulesInterface
{
    public function name(): string;

    public function description(): string;

    public function withGroupAndSensor(GroupInterface $group, SensorInterface $sensor): ProgramInterface;

    /**
     * @return ResourceLinksInterface[]
     */
    public function resourceLinks(): array;
}