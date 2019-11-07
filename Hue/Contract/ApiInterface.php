<?php
declare(strict_types=1);

namespace Hue\Contract;

interface ApiInterface
{
    public function get(string $url): ApiResponseInterface;

    public function delete(string $url): ApiResponseInterface;

    public function put(string $url, array $data): ApiResponseInterface;

    public function post(string $url, array $data): ApiResponseInterface;
}