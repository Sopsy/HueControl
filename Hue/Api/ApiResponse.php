<?php
declare(strict_types=1);

namespace Hue\Api;

use Hue\Contract\ApiResponseInterface;
use RuntimeException;
use stdClass;
use function is_array;
use function json_decode;
use function ob_get_clean;

final class ApiResponse implements ApiResponseInterface
{
    private $response;

    public function __construct(string $response)
    {
        $response = json_decode($response, false);

        // Why? I don't know.
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        if (is_array($response)) {
            $response = $response[0];
        }

        if (isset($response->error)) {
            throw new RuntimeException('Error from Hue API: ' . $response->error->description);
        }

        $this->response = $response;
    }

    public function success(): bool
    {
        if (isset($this->response->error)) {
            return false;
        }

        if (isset($this->response->success)) {
            return true;
        }

        return true;
    }

    public function message(): string
    {
        if (isset($this->response->error)) {
            return $this->response->error->description;
        }

        return $this->response->success ?? '';
    }

    public function data(): stdClass
    {
        return $this->response->success ?? $this->response;
    }

    public function response(): stdClass
    {
        return $this->response;
    }

    public function __toString()
    {
        ob_start();

        var_dump($this->response);

        return ob_get_clean();
    }
}