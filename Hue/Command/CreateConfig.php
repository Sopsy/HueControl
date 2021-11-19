<?php
declare(strict_types=1);

namespace Hue\Command;

use Hue\Api\Api;
use Hue\Config\CustomConfig;
use Hue\Contract\CommandInterface;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use function array_key_exists;
use function count;
use function dirname;
use function fclose;
use function fgets;
use function file_get_contents;
use function file_put_contents;
use function filter_var;
use function fsockopen;
use function is_file;
use function is_resource;
use function json_decode;
use function json_encode;
use function trim;
use const FILTER_VALIDATE_IP;
use const JSON_THROW_ON_ERROR;
use const STDIN;

final class CreateConfig implements CommandInterface
{
    private string $bridgeIp;

    public function run(string ...$args): void
    {
        if (is_file(dirname(__DIR__, 2) . '/config.json')) {
            throw new RuntimeException("Config file already exists. To recreate, delete config.json.");
        }

        if (!isset($args[0])) {
            echo "Detecting Hue Bridges...\n";
            $discoveryUrl = 'https://discovery.meethue.com/';
            $discoveryJson = @file_get_contents($discoveryUrl);

            if ($discoveryJson === false) {
                throw new RuntimeException("Failed to open {$discoveryUrl}");
            }

            try {
                $bridges = json_decode($discoveryJson, false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException('Invalid reply from discovery API, JSON decoding failed', 1, $e);
            }

            if (count($bridges) === 0) {
                throw new RuntimeException('No Hue Bridges found. Try to enter bridge IP manually.');
            }

            if (count($bridges) === 1) {
                $this->bridgeIp = $bridges[0]->internalipaddress;
                $bridgeId = $bridges[0]->id;

                echo "Found bridge: {$this->bridgeIp} (ID: {$bridgeId})\n";
            } else {
                echo "\nFound multiple bridges, please choose one (type a number and press enter):\n";
                foreach ($bridges as $index => $bridge) {
                    echo "{$index}: {$bridge->internalipaddress} (ID: {$bridge->id})\n";
                }
                $userInput = trim(fgets(STDIN));
                $selectedBridge = (int)$userInput;

                if ((string)$selectedBridge !== $userInput || !array_key_exists($selectedBridge, $bridges)) {
                    throw new InvalidArgumentException("Invalid bridge: {$userInput}");
                }

                $this->bridgeIp = $bridges[$selectedBridge]->internalipaddress;
                echo "Bridge {$selectedBridge}: {$this->bridgeIp} selected\n\n";
            }
        } else {
            $this->bridgeIp = $args[0];

            $test = @fsockopen($this->bridgeIp, 80);
            if (!is_resource($test)) {
                throw new InvalidArgumentException("No response from {$this->bridgeIp}");
            }
            fclose($test);
        }

        if (!filter_var($this->bridgeIp, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException("Invalid bridge IP: {$this->bridgeIp}");
        }

        try {
            $config = json_encode([
                'bridgeIp' => $this->bridgeIp,
                'username' => $this->getUsername(),
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('');
        }

        file_put_contents(dirname(__DIR__, 2) . '/config.json', $config);

        echo "Config created";
    }

    private function getUsername(): string
    {
        echo "Please push the link button in the Hue bridge now.\n";
        echo "Press Enter to continue...\n";
        /** @noinspection UnusedFunctionResultInspection */
        fgets(STDIN);
        echo "Connecting to bridge...\n";

        $response = (new Api(new CustomConfig($this->bridgeIp, '')))->post('', ['devicetype' => 'Sopsy/HueControl']);

        return $response->response()->username;
    }
}