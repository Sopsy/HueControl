<?php
declare(strict_types=1);

use Hue\Application;

spl_autoload_register(static function ($className) {
    // Not quite working on Linux, so we need some bubble gum.
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    if (is_file(__DIR__ . "/{$className}.php")) {
        include __DIR__ . "/{$className}.php";
    }
});

set_exception_handler(static function (Throwable $e) {
    echo "ERROR: {$e->getMessage()}\nIn file {$e->getFile()} on line {$e->getLine()}\n";
});

new Application(...$argv);

echo "\n";