<?php

use Hue\Bridge;

spl_autoload_register(function ($className) {
    // Not quite working on Linux, so we need some bubble gum.
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require(__DIR__ . "/{$className}.php");
});

set_exception_handler(static function (Throwable $e) {
    echo "ERROR: {$e->getMessage()}\n";
});

$hue = new Bridge($argv[1], $argv[2]);

if (!isset($argv[3])) {
    $command = false;
} else {
    $command = $argv[3];
}

switch ($command)
{
    case 'get-resource-links':
        $hue->getResourceLinks();

        break;
    case 'get-lights':
        $hue->getLights();

        break;
    case 'get-rules':
        $hue->getRules();

        break;
    case 'get-groups':
        $hue->getGroups();

        break;
    case 'get-scenes':
        $hue->getScenes($argv[4] ?? null);

        break;
    case 'get-sensors':
        $hue->getSensors();

        break;
    case 'delete-unused-memory-sensors':
        $hue->deleteUnusedMemorySensors();

        break;
    case 'program-sensor':
        if (empty($argv[4])) {
            echo 'Missing sensor name';
            break;
        }

        if (empty($argv[5])) {
            echo 'Missing group (room) name';
            break;
        }

        if (empty($argv[6])) {
            echo 'Missing program name';
            break;
        }

        $hue->programSensor($argv[4], $argv[5], $argv[6]);

        break;
    default:
        echo 'Usage: php cli.php <bridge ip> <username> <command>';

        break;
}

echo "\n";