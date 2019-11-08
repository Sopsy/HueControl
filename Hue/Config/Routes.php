<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @var $this \Hue\Application */
declare(strict_types=1);

return [
    'get-lights' => function() {
        return new \Hue\RequestHandler\GetLights($this->bridge);
    },
    'get-resource-links' => function() {
        return new \Hue\RequestHandler\GetResourceLinks($this->bridge);
    },
    'get-rules' => function() {
        return new \Hue\RequestHandler\GetRules($this->bridge);
    },
    'get-groups' => function() {
        return new \Hue\RequestHandler\GetGroups($this->bridge);
    },
    'get-scenes' => function() {
        return new \Hue\RequestHandler\GetScenes($this->bridge);
    },
    'get-sensors' => function() {
        return new \Hue\RequestHandler\GetSensors($this->bridge);
    },
    'delete-unused-memory-sensors' => function() {
        return new \Hue\RequestHandler\DeleteUnusedMemorySensors($this->bridge);
    },
    'program-sensor' => function() {
        return new \Hue\RequestHandler\ProgramSensor($this->bridge);
    },
];