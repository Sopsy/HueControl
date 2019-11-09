<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\SensorGroup;
use Hue\Resource\Sensor;
use Hue\Resource\SensorGeneric;
use Hue\Resource\SensorTemp;
use function in_array;
use function uniqid;
use function var_dump;

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
            if ($sensor->type === 'ZLLTemperature') {
                $sensors[] = new SensorTemp((int)$id, $sensor->name, $sensor->type, $sensor->modelid, $sensor->state->temperature);
            } else {
                $sensors[] = new SensorGeneric((int)$id, $sensor->name, $sensor->type, $sensor->modelid);
            }
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

        return new SensorGeneric((int)$response->data()->id, $name, $type, $modelId);
    }

    public function delete(int $id): void
    {
        $this->api->delete('/sensors/' . $id);
    }

    public function deleteUnusedGeneric(): void
    {
        $ruleRepo = new RuleRepository($this->api);
        $sensorRepo = new SensorRepository($this->api);

        $unusedSensors = [];
        foreach ($sensorRepo->getAll()->all() as $sensor) {
            if (in_array($sensor->type(), ['CLIPGenericStatus', 'CLIPGenericFlag'])) {
                $unusedSensors[$sensor->id()] = $sensor;
            }
        }

        foreach ($unusedSensors as $sensorId => $sensor) {
            foreach ($ruleRepo->getAll()->all() as $rule) {
                foreach ($rule->conditions() as $condition) {
                    if ($condition->address === "/sensors/{$sensorId}/state/status") {
                        unset($unusedSensors[$sensorId]);
                        continue 3;
                    }
                }
            }
        }

        foreach ($unusedSensors as $sensorId => $sensor) {
            $this->delete($sensorId);
            echo "Deleted unused sensor: {$sensor->id()} ({$sensor->name()})\n";
        }
    }
}