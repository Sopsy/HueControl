<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\ScheduleGroup;
use Hue\Resource\Schedule;

final class ScheduleRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $data = ($this->api->get('/schedules'))->data();

        $sensors = [];
        foreach ($data as $id => $sensor) {
            $sensors[] = new Schedule((int)$id, $sensor->name);
        }

        return new ScheduleGroup(...$sensors);
    }

    public function create(string $name, string $command, string $time, bool $autodelete = true): Schedule
    {
        $data = [
            'name' => $name,
            'command' => $command,
            'localtime' => $time,
            'autodelete' => $autodelete,
            'recycle' => true,
        ];

        $response = $this->api->post('/schedules/', $data);

        return new Schedule((int)$response->data()->id, $name);
    }

    public function delete(int $id): void
    {
        $this->api->delete('/schedules/' . $id);
    }
}