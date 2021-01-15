<?php
declare(strict_types=1);

namespace Hue\Api;

use Hue\Contract\ApiInterface;
use Hue\Contract\ApiResponseInterface;
use Hue\Contract\ConfigInterface;
use JsonException;
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
use const JSON_THROW_ON_ERROR;

final class Api implements ApiInterface
{
    public function __construct(private ConfigInterface $config)
    {
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
        $ch = curl_init('http://' . $this->config->bridgeIp() . '/api/' . $this->config->username() . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST' || $method === 'PUT') {
            try {
                $payload = json_encode($data, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException('JSON encoding payload data failed', 1, $e);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Could not get a response from Hue API.');
        }

        return new ApiResponse($response);
    }
}