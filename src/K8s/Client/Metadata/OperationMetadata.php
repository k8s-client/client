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

use K8s\Core\Annotation\Operation;

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

    public function isResponseSelf(): bool
    {
        return $this->operation->response === 'static::class';
    }

    public function getResponseFqcn(): ?string
    {
        if ($this->isResponseSelf()) {
            return null;
        }

        return $this->operation->response;
    }

    public function getKubernetesAction(): string
    {
        return self::ACTIONS[$this->operation->type] ?? $this->operation->type;
    }
}
