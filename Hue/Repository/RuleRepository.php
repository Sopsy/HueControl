<?php
declare(strict_types=1);

namespace Hue\Repository;

use Hue\Contract\ApiInterface;
use Hue\Contract\GroupInterface;
use Hue\Group\RuleGroup;
use Hue\Resource\Rule;

final class RuleRepository
{
    private $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getAll(): GroupInterface
    {
        $data = ($this->api->get('/rules'))->data();

        $rules = [];
        foreach ($data as $id => $rule) {
            $rules[] = new Rule((int)$id, $rule->name, $rule->conditions, $rule->actions);
        }

        return new RuleGroup(...$rules);
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

        return new Rule((int)$response->data()->id, $name, $conditions, $actions);
    }

    public function delete(int $id): void
    {
        $this->api->delete('/rules/' . $id);
    }
}