<?php

use Hue\Application;

spl_autoload_register(function ($className) {
    // Not quite working on Linux, so we need some bubble gum.
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require(__DIR__ . "/{$className}.php");
});

set_exception_handler(static function (Throwable $e) {
    echo "ERROR: {$e->getMessage()} ({$e->getFile()}:{$e->getLine()})\n";
});

new Application(...$argv);

echo "\n";