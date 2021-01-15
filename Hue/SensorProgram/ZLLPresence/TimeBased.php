<?php
declare(strict_types=1);

namespace Hue\SensorProgram\ZLLPresence;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\ProgramInterface;
use Hue\Contract\SensorInterface;
use Hue\Repository\SceneRepository;
use Hue\Resource\Rule;

final class TimeBased implements ProgramInterface
{
    private GroupInterface $group;
    private SensorInterface $sensor;
    private array $resourceLinks = [];

    public function __construct(private ApiInterface $api)
    {
    }

    public function name(): string
    {
        return 'Time based';
    }

    public function description(): string
    {
        return 'Like the Hue default program for a motion sensor';
    }

    public function rules(): array
    {
        $return = [];

        $scenes = new SceneRepository($this->api);
        $energize = $scenes->byGroupAndName($this->group, 'Energize');
        $concentrate = $scenes->byGroupAndName($this->group, 'Concentrate');
        $read = $scenes->byGroupAndName($this->group, 'Read');
        $relax = $scenes->byGroupAndName($this->group, 'Relax');
        $nightlight = $scenes->byGroupAndName($this->group, 'Nightlight');

        $this->resourceLinks[] = $this->group;
        $this->resourceLinks[] = $this->sensor;
        $this->resourceLinks[] = $energize;
        $this->resourceLinks[] = $concentrate;
        $this->resourceLinks[] = $read;
        $this->resourceLinks[] = $relax;
        $this->resourceLinks[] = $nightlight;

        // 05:30 - 11:00
        $return[] = new Rule(
            0,
            "Motion {$this->sensor->id()} presence morning",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'eq', 'value' => 'true'],
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T05:30:00/T11:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ],
            [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $energize->id()]],
            ]
        );

        // 11:00 - 17:00
        $return[] = new Rule(
            0,
            "Motion {$this->sensor->id()} presence day",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'eq', 'value' => 'true'],
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T11:00:00/T17:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ],
            [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $concentrate->id()]],
            ]
        );

        // 17:00 - 20:00
        $return[] = new Rule(
            0,
            "Motion {$this->sensor->id()} presence evening",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'eq', 'value' => 'true'],
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T17:00:00/T20:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ], [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $read->id()]],
            ]
        );

        // 20:00 - 23:00
        $return[] = new Rule(
            0,
            "Motion {$this->sensor->id()} presence late",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'eq', 'value' => 'true'],
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T20:00:00/T00:00:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ],
            [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $relax->id()]],
            ]
        );

        // 23:00 - 05:30
        $return[] = new Rule(
            0,
            "Motion {$this->sensor->id()} presence night",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'eq', 'value' => 'true'],
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'dx'],
                ['address' => '/config/localtime', 'operator' => 'in', 'value' => 'T00:00:00/T05:30:00'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'false'],
            ],
            [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['scene' => $nightlight->id()]],
            ]
        );

        // No presence
        $return[] = new Rule(
            0,
            "Motion {$this->sensor->id()} no-presence",
            [
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'eq', 'value' => 'false'],
                ['address' => "{$this->sensor->apiStateUrl()}/presence", 'operator' => 'ddx', 'value' => 'PT00:09:15'],
                ['address' => "{$this->group->apiStateUrl()}/any_on", 'operator' => 'eq', 'value' => 'true'],
            ],
            [
                ['address' => "{$this->group->apiUrl()}/action", 'method' => 'PUT', 'body' => ['on' => false]],
            ]
        );

        return $return;
    }

    public function resourceLinks(): array
    {
        return $this->resourceLinks;
    }

    public function withGroupAndSensor(GroupInterface $group, SensorInterface $sensor): ProgramInterface
    {
        $clone = clone $this;
        $clone->group = $group;
        $clone->sensor = $sensor;

        return $clone;
    }
}