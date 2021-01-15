<?php
declare(strict_types=1);

use Hue\Bootstrap;
use Hue\Config\JsonConfig;
use Hue\Config\NoConfig;

spl_autoload_register(static function ($className) {
    $classFile = __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    if (is_file($classFile)) {
        include $classFile;
    }
});

// Load config
if (is_file(__DIR__ . '/config.json')) {
    $config = new JsonConfig(__DIR__ . '/config.json');
} else {
    $config = new NoConfig();
}

// Remove cli.php
unset($argv[0]);

try {
    new Bootstrap($config, ...$argv);
} catch (Throwable $e) {
    echo "{$e->getMessage()}";
}

echo "\n";