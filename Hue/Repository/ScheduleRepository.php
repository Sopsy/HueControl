<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Resource\Schedule;
use function json_encode;

final class ScheduleRepository
{
    public function __construct(private ApiInterface $api)
    {
    }

    public function all(): array
    {
        $data = $this->api->get('/schedules');

        $return = [];
        foreach ($data->response() as $id => $schedule) {
            $return[] = new Schedule(
                (int)$id,
                $schedule->name,
                $schedule->description,
                json_encode($schedule->command, JSON_THROW_ON_ERROR),
                $schedule->localtime,
                $schedule->time,
                $schedule->status
            );
        }

        return $return;
    }

    public function create(string $name, string $command, string $time, bool $autoDelete = true): Schedule
    {
        $data = [
            'name' => $name,
            'command' => $command,
            'localtime' => $time,
            'autodelete' => $autoDelete,
            'recycle' => true,
        ];

        $response = $this->api->post('/schedules/', $data);

        return new Schedule(
            (int)$response->response()->id,
            $name,
            $response->response()->description ?? '',
            $command,
            $time,
            $response->response()->time ?? '',
            $response->response()->status ?? 'enabled'
        );
    }

    public function delete(int $id): void
    {
        $this->api->delete('/schedules/' . $id);
    }
}