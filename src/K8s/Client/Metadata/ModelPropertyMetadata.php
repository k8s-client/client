<?php

/**
 * This file is part of the k8s/client library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace K8s\Client\Metadata;

use K8s\Core\Annotation\Attribute;

class ModelPropertyMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Attribute
     */
    private $attribute;

    public function __construct(string $name, Attribute $attribute)
    {
        $this->name = $name;
        $this->attribute = $attribute;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributeName(): string
    {
        return $this->attribute->name;
    }

    public function isCollection(): bool
    {
        return $this->attribute->type === 'collection';
    }

    public function isDateTime(): bool
    {
        return $this->attribute->type === 'datetime';
    }

    public function isModel(): bool
    {
        return $this->attribute->type === 'model';
    }

    /**
     * @return class-string|null
     */
    public function getModelFqcn(): ?string
    {
        return $this->attribute->model;
    }
}
