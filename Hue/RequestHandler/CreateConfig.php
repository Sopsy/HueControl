<?php
declare(strict_types=1);

namespace Hue\RequestHandler;

use Hue\Contract\RequestHandlerInterface;
use RuntimeException;
use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function dirname;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function strlen;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;

final class CreateConfig implements RequestHandlerInterface
{
    private $bridgeIp;

    public function __construct(string $bridgeIp)
    {
        $this->bridgeIp = $bridgeIp;
    }

    public function handle(string ...$args): void
    {
        if ((empty($args[0]) || $args[0] !== '--force') && is_file(dirname(__DIR__, 2) . '/config.json')) {
            echo "Config file already exists. To force recreation, use --force\n";

            return;
        }

        $config = json_encode([
            'bridgeIp' => $this->bridgeIp,
            'username' => $this->getUsername(),
        ]);

        file_put_contents(dirname(__DIR__, 2) . '/config.json', $config);

        echo "Config created\n";
    }

    private function getUsername(): string
    {
        $ch = curl_init('http://' . $this->bridgeIp . '/api/');
        $payload = '{"devicetype": "Sopsy/Hue"}';

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Could not get a response from Hue API, curl returned false.');
        }

        $response = json_decode($response)[0];

        if(!empty($response->error)) {
            throw new RuntimeException($response->error->description);
        }

        return $response->success->username;
    }
}