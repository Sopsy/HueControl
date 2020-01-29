<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Contract\ResourceInterface;
use Hue\Group\ResourceLinksGroup;
use Hue\Group\RuleGroup;
use Hue\Resource\ResourceLinks;
use Hue\Resource\Rule;

final class ResourceLinksRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $data = ($this->api->get('/resourcelinks'))->data();

        $links = [];
        foreach ($data as $id => $link) {
            $links[] = new ResourceLinks((int)$id, $link->name, $link->classid, $link->links);
        }

        return new ResourceLinksGroup(...$links);
    }

    /**
     * @param string $name
     * @param string $description
     * @param int $classId
     * @param ResourceInterface[] $links
     * @return ResourceLinks
     */
    public function create(string $name, string $description, int $classId, array $links): ResourceLinks
    {
        $resourceLinks = [];
        foreach ($links as $link) {
            $resourceLinks[] = $link->apiUrl();
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'classid' => $classId,
            'recycle' => true,
            'links' => $resourceLinks
        ];

        $response = $this->api->post('/resourcelinks', $data);

        return new ResourceLinks((int)$response->data()->id, $name, $classId, $links);
    }

    public function delete(int $id): void
    {
        $this->api->delete('/resourcelinks/' . $id);
    }
}