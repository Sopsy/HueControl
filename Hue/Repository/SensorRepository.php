<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\SensorInterface;
use Hue\Resource\Sensor;
use Hue\Resource\TempSensor;
use InvalidArgumentException;
use stdClass;
use function in_array;
use function uniqid;

final class SensorRepository
{
    public const TYPE_SWITCH = 'ZLLSwitch';
    public const TYPE_TAP_SWITCH = 'ZGPSwitch';
    public const TYPE_PRESENCE = 'ZLLPresence';
    public const TYPE_TEMP = 'ZLLTemperature';
    public const TYPE_LIGHT_LEVEL = 'ZLLLightLevel';
    public const TYPE_GENERIC_STATUS = 'CLIPGenericStatus';
    public const TYPE_GENERIC_FLAG = 'CLIPGenericFlag';

    public function __construct(private ApiInterface $api)
    {
    }

    /**
     * @param string $type
     * @return SensorInterface[]
     */
    public function all(string $type = ''): array
    {
        $data = $this->api->get('/sensors');

        $return = [];
        foreach ($data->response() as $id => $sensor) {
            if ($type !== '' && $sensor->type !== $type) {
                continue;
            }

            $return[(int)$id] = $this->createObject((int)$id, $sensor);
        }

        return $return;
    }

    public function byName(string $name = ''): SensorInterface
    {
        foreach ($this->all() as $id => $sensor) {
            if ($sensor->name() === $name) {
                return $sensor;
            }
        }

        throw new InvalidArgumentException("Sensor {$name} not found");
    }

    public function byId(int $id): SensorInterface
    {
        $data = $this->api->get("/sensors/{$id}");

        return $this->createObject($id, $data->response());
    }

    public function createStatus(string $name): Sensor
    {
        return $this->createMemory($name, self::TYPE_GENERIC_STATUS);
    }

    public function createFlag(string $name): Sensor
    {
        return $this->createMemory($name, self::TYPE_GENERIC_FLAG);
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

        return new Sensor((int)$response->response()->id, $name, $type, $modelId);
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
        foreach ($sensorRepo->all() as $sensor) {
            if (in_array($sensor->type(), [self::TYPE_GENERIC_STATUS, self::TYPE_GENERIC_FLAG], true)) {
                $unusedSensors[$sensor->id()] = $sensor;
            }
        }

        $rules = $ruleRepo->all();
        foreach ($unusedSensors as $sensorId => $sensor) {
            foreach ($rules as $rule) {
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

    private function createObject(int $id, stdClass $sensor): SensorInterface
    {
        $return = new Sensor($id, $sensor->name, $sensor->type, $sensor->modelid);

        $return = match ($sensor->type) {
            self::TYPE_TEMP => new TempSensor($return, $sensor->manufacturername, $sensor->productname, $sensor->state->temperature),
            default => $return,
        };

        return $return;
    }
}