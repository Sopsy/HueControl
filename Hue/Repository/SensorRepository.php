<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\SensorGroup;
use Hue\Resource\Sensor;
use function array_key_exists;
use function in_array;
use function str_replace;
use function strpos;
use function uniqid;

final class SensorRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $data = ($this->api->get('/sensors'))->data();

        $sensors = [];
        foreach ($data as $id => $sensor) {
            $sensors[] = new Sensor((int)$id, $sensor->name, $sensor->type, $sensor->modelid);
        }

        return new SensorGroup(...$sensors);
    }

    public function createStatus(string $name): Sensor
    {
        return $this->createMemory($name, 'CLIPGenericStatus');
    }

    public function createFlag(string $name): Sensor
    {
        return $this->createMemory($name, 'CLIPGenericFlag');
    }

    private function createMemory(string $name, string $type): Sensor
    {
        $modelId = 'GenericHueMemory';

        $data = [
            'manufacturername' => 'Sopsy/Hue',
            'modelid' => $modelId,
            'name' => $name,
            'swversion' => '1.0',
            'type' => $type,
            'uniqueid' => uniqid('', true),
            'recycle' => true,
        ];

        $response = $this->api->post('/sensors/', $data);

        return new Sensor((int)$response->data()->id, $name, $type, $modelId);
    }

    public function delete(int $id): void
    {
        $this->api->delete('/sensors/' . $id);
    }

    public function deleteUnusedGeneric(): void
    {
        $resourceLinkRepo = new ResourceLinkRepository($this->api);
        $sensorRepo = new SensorRepository($this->api);

        $unusedSensors = [];
        foreach ($sensorRepo->getAll()->all() as $sensor) {
            if (in_array($sensor->type(), ['CLIPGenericStatus', 'CLIPGenericFlag'])) {
                $unusedSensors[$sensor->id()] = $sensor;
            }
        }

        foreach ($resourceLinkRepo->getAll()->all() as $resourceLink) {
            foreach ($resourceLink->links() as $link) {
                if (strpos($link, '/sensors/') !== 0) {
                    continue;
                }
                $sensorId = (int)str_replace('/sensors/', '', $link);

                // Skip non-generic sensors
                if (!array_key_exists($sensorId, $unusedSensors)) {
                    continue;
                }

                unset($unusedSensors[$sensorId]);
            }
        }

        foreach ($unusedSensors as $sensorId => $sensor) {
            $this->delete($sensorId);
            echo "Deleted unused sensor: {$sensor->id()} ({$sensor->name()})\n";
        }
    }
}