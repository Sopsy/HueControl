<?php
declare(strict_types=1);

namespace Hue\Resource;

use Hue\Contract\HasGamut;
use Hue\Contract\HasModel;
use Hue\Contract\HasSetStateUrl;
use Hue\Contract\IsProduct;
use Hue\Contract\TypedResourceInterface;

final class Light implements TypedResourceInterface, HasModel, HasGamut, IsProduct, HasSetStateUrl
{
    private $id;
    private $name;
    private $type;
    private $model;
    private $gamutType;
    private $manufacturer;
    private $productName;

    public function __construct(
        int $id,
        string $name,
        string $type,
        string $model,
        string $gamutType,
        string $manufacturer,
        string $productName
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->model = $model;
        $this->gamutType = $gamutType;
        $this->manufacturer = $manufacturer;
        $this->productName = $productName;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function model(): string
    {
        return $this->model;
    }

    public function gamutType(): string
    {
        return $this->gamutType;
    }

    public function manufacturer(): string
    {
        return $this->manufacturer;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function apiUrl(): string
    {
        return "/lights/{$this->id()}";
    }

    public function apiSetStateUrl(): string
    {
        return "{$this->apiUrl()}/state";
    }
}