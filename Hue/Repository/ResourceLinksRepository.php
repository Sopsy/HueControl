<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\ApiResponseInterface;
use Hue\Contract\ResourceInterface;
use Hue\Contract\ResourceLinksInterface;
use Hue\Resource\ResourceLinks;

final class ResourceLinksRepository
{
    public function __construct(private ApiInterface $api)
    {
    }

    /**
     * @return ResourceLinksInterface[]
     */
    public function all(): array
    {
        $data = $this->api->get('/resourcelinks');

        $return = [];
        foreach ($data->response() as $id => $link) {
            $return[] = new ResourceLinks((int)$id, $link->name, (int)$link->classid, $link->links);
        }

        return $return;
    }

    public function byId(int $id): ResourceLinksInterface
    {
        $link = $this->api->get("/resourcelinks/{$id}")->response();

        return new ResourceLinks($id, $link->name, (int)$link->classid, $link->links);
    }

    /**
     * @param string $name
     * @param string $description
     * @param int $classId
     * @param ResourceInterface[] $links
     * @return ResourceLinksInterface
     */
    public function create(string $name, string $description, int $classId, array $links): ResourceLinksInterface
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

        return new ResourceLinks((int)$response->response()->id, $name, $classId, $links);
    }

    public function delete(int $id): ApiResponseInterface
    {
        return $this->api->delete('/resourcelinks/' . $id);
    }
}