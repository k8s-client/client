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

class FullContext
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Cluster
     */
    private $cluster;

    /**
     * @var User
     */
    private $user;

    public function __construct(Context $context, Cluster $cluster, User $user)
    {
        $this->context = $context;
        $this->cluster = $cluster;
        $this->user = $user;
    }

    public function getUserClientCertificate(): ?string
    {
        return $this->user->getClientCertificate();
    }

    public function getUserClientCertificateData(): ?string
    {
        return $this->user->getClientCertificateData();
    }

    public function getUserClientKey(): ?string
    {
        return $this->user->getClientKey();
    }

    public function getUserClientKeyData(): ?string
    {
        return $this->user->getClientKeyData();
    }

    public function getUserToken(): ?string
    {
        return $this->user->getToken();
    }

    public function getUserTokenFile(): ?string
    {
        return $this->user->getTokenFile();
    }

    public function getUserUsername(): ?string
    {
        return $this->user->getUsername();
    }

    public function getUserPassword(): ?string
    {
        return $this->user->getPassword();
    }

    public function getServerCertificateAuthority(): ?string
    {
        return $this->cluster->getCertificateAuthority();
    }

    public function getServerCertificateAuthorityData(): ?string
    {
        return $this->cluster->getCertificateAuthority();
    }

    public function getAuthType(): string
    {
        return $this->user->getAuthType();
    }

    public function getServer(): string
    {
        return $this->cluster->getServer();
    }

    public function getNamespace(): ?string
    {
        return $this->context->getNamespace();
    }
}
