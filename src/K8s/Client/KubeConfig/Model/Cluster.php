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

class Cluster
{
    /**
     * @var array<string, mixed>
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getCertificateAuthority(): ?string
    {
        return $this->data['cluster']['certificate-authority'] ?? null;
    }

    public function getCertificateAuthorityData(): ?string
    {
        return $this->data['cluster']['certificate-authority-data'] ?? null;
    }

    public function getServer(): string
    {
        return $this->data['cluster']['server'];
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
