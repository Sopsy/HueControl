<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\ResourceLinksGroup;
use Hue\Resource\ResourceLinks;
use RuntimeException;

final class ResourceLinkRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $data = ($this->api->get('/resourcelinks'))->data();

        $resourceLinks = [];
        foreach ($data as $id => $resourceLink) {
            $resourceLinks[] = new ResourceLinks((int)$id, $resourceLink->name, $resourceLink->type, $resourceLink->links);
        }

        return new ResourceLinksGroup(...$resourceLinks);
    }

    public function delete(int $id): string
    {
        $response = $this->api->delete('/resourcelinks/' . $id);

        if (!$response->success()) {
            throw new RuntimeException("Could not delete Resource link '{$id}': " . $response);
        }

        return $response->message();
    }
}