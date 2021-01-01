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

use K8s\Client\Exception\RuntimeException;

class ModelMetadata
{
    /**
     * @var string
     */
    private $modelFqcn;

    /**
     * @var ModelPropertyMetadata[]
     */
    private $properties;

    /**
     * @var OperationMetadata[]
     */
    private $operations;

    /**
     * @var KindMetadata|null
     */
    private $kind;

    /**
     * @param ModelPropertyMetadata[] $properties
     * @param OperationMetadata[] $operations
     */
    public function __construct(
        string $modelFqcn,
        array $properties,
        array $operations,
        ?KindMetadata $kind
    ) {
        $this->modelFqcn = $modelFqcn;
        $this->properties = $properties;
        $this->operations = $operations;
        $this->kind = $kind;
    }

    /**
     * @return string
     */
    public function getModelFqcn(): string
    {
        return $this->modelFqcn;
    }

    /**
     * @return ModelPropertyMetadata[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return OperationMetadata[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getKind(): ?KindMetadata
    {
        return $this->kind;
    }

    public function getOperationByType(string $type): OperationMetadata
    {
        foreach ($this->operations as $operation) {
            if ($operation->getType() === $type) {
                return $operation;
            }
        }

        if ($this->kind) {
            $message = sprintf(
                'Kind "%s" with version "%s" (%s)',
                $this->kind->getKind(),
                $this->kind->getVersion(),
                $this->getModelFqcn()
            );
        } else {
            $message = sprintf('class %s', $this->getModelFqcn());
        }

        throw new RuntimeException(sprintf(
            'The operation type "%s" is not recognised on %s.',
            $type,
            $message
        ));
    }
}
