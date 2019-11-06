<?php
declare(strict_types=1);

namespace Hue;

use InvalidArgumentException;
use RuntimeException;
use function var_dump;
use const FILTER_VALIDATE_IP;

final class Bridge
{
    private $user;
    private $bridgeIp;

    private $groups = [];
    private $scenes = [];

    public function __construct(string $bridgeIp, string $user)
    {
        if (!filter_var($bridgeIp, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid bridge IP');
        }

        $this->user = $user;
        $this->bridgeIp = $bridgeIp;

        $this->loadData();
    }

    private function loadData()
    {
        $json = file_get_contents("http://{$this->bridgeIp}/api/{$this->user}/");

        if (!$json) {
            throw new RuntimeException("Could not connect to the bridge at {$this->bridgeIp}.");
        }

        $data = json_decode($json, false);

        $groups = [];
        foreach ($data->groups AS $groupId => $group) {
            $lights = [];
            foreach ($group->lights AS $lightId) {
                $lights[] = new Light((int)$lightId, $data->lights->$lightId->name, $data->lights->$lightId->type);
            }
            $lights = new LightGroup(...$lights);

            $scenes = [];
            foreach ($data->scenes AS $sceneId => $scene) {
                if ($scene->type !== 'GroupScene' || $scene->group !== $groupId) {
                    continue;
                }

                $scenes[] = new Scene($sceneId, $scene->name, $scene->type);
            }
            $scenes = new SceneGroup(...$scenes);

            $groups[(int)$groupId] = new Group((int)$groupId, $group->name, $group->class, $group->type, $lights, $scenes);
        }

        $this->groups = $groups;
        var_dump($this->groups);
    }
}