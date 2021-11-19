<?php
declare(strict_types=1);

namespace Hue\Api;

use Hue\Contract\ApiResponseInterface;
use JsonException;
use RuntimeException;
use stdClass;
use function is_array;
use function json_decode;
use function ob_get_clean;
use function ob_start;
use function var_dump;
use const JSON_THROW_ON_ERROR;

final class ApiResponse implements ApiResponseInterface
{
    private stdClass $response;

    public function __construct(string $responseString)
    {
        try {
            $response = json_decode($responseString, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Decoding API response JSON failed', 1, $e);
        }

        if (is_array($response)) {
            $response = $response[0];
        }

        $this->response = $response;

        if (!$this->success()) {
            throw new RuntimeException('Error from Hue API: ' . $this->message());
        }
    }

    public function success(): bool
    {
        return !isset($this->response->error);
    }

    public function message(): string
    {
        if (!$this->success()) {
            return $this->response->error->description;
        }

        return $this->response->success ?? '';
    }

    public function response(): stdClass
    {
        return $this->response->success ?? $this->response;
    }

    public function __toString(): string
    {
        ob_start();

        var_dump($this->response);

        return (string)ob_get_clean();
    }
}