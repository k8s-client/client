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

namespace K8s\Client\KubeConfig\Model;

class Context
{
    /**
     * @var array<string, mixed>
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getClusterName(): string
    {
        return $this->data['context']['cluster'];
    }

    public function getUserName(): string
    {
        return $this->data['context']['user'];
    }

    public function getNamespace(): ?string
    {
        return $this->data['context']['namespace'] ?? null;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
