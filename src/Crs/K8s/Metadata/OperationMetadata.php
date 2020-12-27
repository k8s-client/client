<?php

/**
 * This file is part of the crs/k8s library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crs\K8s\Metadata;

use Crs\K8s\Annotation\Operation;

class OperationMetadata
{
    private const ACTIONS = [
        'watch-all' => 'watch',
        'list-all' => 'list',
        'deletecollection-all' => 'deletecollection',
    ];

    /**
     * @var Operation
     */
    private $operation;

    public function __construct(Operation $operation)
    {
        $this->operation = $operation;
    }

    public function getType(): string
    {
        return $this->operation->type;
    }

    public function getPath(): string
    {
        return $this->operation->path;
    }

    public function isBodyRequired(): bool
    {
        return !empty($this->operation->body);
    }

    public function getResponseFqcn(): ?string
    {
        return $this->operation->response;
    }

    public function getKubernetesAction(): string
    {
        return self::ACTIONS[$this->operation->type] ?? $this->operation->type;
    }
}
