<?php
declare(strict_types=1);

namespace Hue\Program\DimmerSwitch;

use Hue\Contract\ApiInterface;

abstract class AbstractDimmerSwitchProgram
{
    protected $output = '';
    protected $api;
    protected $switchName;
    protected $groupName;

    public function __construct(ApiInterface $api, string $switchName, string $groupName)
    {
        $this->api = $api;
        $this->switchName = $switchName;
        $this->groupName = $groupName;
    }

    public function output(): string
    {
        return $this->output;
    }
}