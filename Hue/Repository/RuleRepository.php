<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\ApiResponseInterface;
use Hue\Resource\Rule;

final class RuleRepository
{
    public function __construct(private ApiInterface $api)
    {
    }

    public function all(): array
    {
        $data = $this->api->get('/rules');

        $return = [];
        foreach ($data->response() as $id => $rule) {
            $return[] = new Rule((int)$id, $rule->name, $rule->conditions, $rule->actions);
        }

        return $return;
    }

    public function create(string $name, array $conditions, array $actions): Rule
    {
        $data = [
            'name' => $name,
            'conditions' => $conditions,
            'actions' => $actions,
            'recycle' => true,
        ];

        $response = $this->api->post('/rules', $data);

        return new Rule((int)$response->response()->id, $name, $conditions, $actions);
    }

    public function delete(int $id): ApiResponseInterface
    {
        return $this->api->delete('/rules/' . $id);
    }
}