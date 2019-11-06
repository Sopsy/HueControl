<?php

use Hue\Bridge;

spl_autoload_register(function ($className) {
    // Not quite working on Linux, so we need some bubble gum.
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require(__DIR__ . "/{$className}.php");
});

$hue = new Bridge($argv[1], $argv[2]);

