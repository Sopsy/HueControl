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
    case 'get-scenes':
        if (empty($argv[4]) || (int)$argv[4] === 0) {
            echo 'Missing or invalid group id';
            break;
        }
        echo $hue->getScenes((int)$argv[4]);
        break;
    default:
        echo 'Usage: php cli.php <bridge ip> <username> <command>';
        break;
}

echo "\n";