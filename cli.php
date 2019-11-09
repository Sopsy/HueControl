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

if (is_file(__DIR__ . '/config.json')) {
    $config = json_decode(file_get_contents(__DIR__ . '/config.json'));
} else {
    $config = new stdClass();
}

if (empty($config->username)) {
    $config->username = '';
}

if (empty($config->bridgeIp)) {
    echo "Missing Bridge IP-address, trying to autodetect...\n";
    $data = @file_get_contents('https://www.meethue.com/api/nupnp');

    if (!$data) {
        echo "Failed to open https://www.meethue.com/api/nupnp\n";
        die();
    }

    $data = json_decode($data)[0];
    $config->bridgeIp = trim($data->internalipaddress);

    echo "Found Bridge IP: {$config->bridgeIp}\n";
}

// Remove cli.php
unset($argv[0]);

new Application($config->bridgeIp, $config->username, ...$argv);

echo "\n";