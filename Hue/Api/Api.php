<?php
declare(strict_types=1);

namespace Hue\Api;

use Hue\Contract\ApiInterface;
use Hue\Contract\ApiResponseInterface;
use RuntimeException;
use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function json_encode;
use function strlen;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;

final class Api implements ApiInterface
{
    private $bridgeIp;
    private $username;

    public function __construct(string $bridgeIp, string $username)
    {
        $this->bridgeIp = $bridgeIp;
        $this->username = $username;
    }

    public function get(string $url): ApiResponseInterface
    {
        return $this->curl('GET', $url);
    }

    public function delete(string $url): ApiResponseInterface
    {
        return $this->curl('DELETE', $url);
    }

    public function put(string $url, array $data): ApiResponseInterface
    {
        return $this->curl('PUT', $url, $data);
    }

    public function post(string $url, array $data): ApiResponseInterface
    {
        return $this->curl('POST', $url, $data);
    }

    private function curl(string $method, string $url, array $data = []): ApiResponseInterface
    {
        $ch = curl_init('http://' . $this->bridgeIp . '/api/' . $this->username . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);

                $payload = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ]);

                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                $payload = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ]);

                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                throw new RuntimeException("Invalid request method {$method}.");
        }

        $response = curl_exec($ch);

        curl_close($ch);

        $this->checkResponse($response);

        return new ApiResponse($response);
    }

    private function checkResponse($response): void
    {
        if ($response === false) {
            throw new RuntimeException('Could not get a response from Hue API, curl returned false.');
        }
    }
}