<?php

use Hue\Bridge;

spl_autoload_register(function ($className) {
    // Not quite working on Linux, so we need some bubble gum.
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require(__DIR__ . "/{$className}.php");
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
        echo $hue->getResourceLinks();

        break;
    case 'get-groups':
        echo $hue->getGroups();

        break;
    case 'get-sensors':
        echo $hue->getSensors();

        break;
    case 'program-switch':
        if (empty($argv[4])) {
            echo 'Missing switch name';

            break;
        }
        if (empty($argv[5])) {
            echo 'Missing group (room) name';

            break;
        }
        echo $hue->programDimmerSwitch($argv[4], $argv[5]);

        break;
    case 'get-scenes':
        if (empty($argv[4])) {
            echo 'Missing group name';
            break;
        }
        echo $hue->getScenes($argv[4]);

        break;
    default:
        echo 'Usage: php cli.php <bridge ip> <username> <command>';

        break;
}

echo "\n";