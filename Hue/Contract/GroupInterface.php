<?php
declare(strict_types=1);

namespace Hue\Contract;

interface GroupInterface extends TypedResourceInterface, HasState
{
    public function class(): string;

    /**
     * @return LightInterface[]
     */
    public function lights(): array;

    /**
     * @return SceneInterface[]
     */
    public function scenes(): array;
}