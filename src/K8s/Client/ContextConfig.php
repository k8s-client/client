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

namespace K8s\Client;

use K8s\Client\KubeConfig\Model\FullContext;
use K8s\Core\Contract\ContextConfigInterface;

class ContextConfig implements ContextConfigInterface
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var FullContext|null
     */
    private $context;

    public function __construct(Options $options, ?FullContext $context = null)
    {
        $this->options = $options;
        $this->context = $context;
    }

    public function getAuthType(): string
    {
        if ($this->context) {
            return $this->context->getAuthType();
        }

        return $this->options->getAuthType();
    }

    public function getClientKeyData(): ?string
    {
        if ($this->context) {
            return $this->context->getUserClientKeyData();
        }

        return null;
    }

    public function getClientKey(): ?string
    {
        if ($this->context) {
            return $this->context->getUserClientKey();
        }

        return null;
    }

    public function getClientCertificate(): ?string
    {
        if ($this->context) {
            return $this->context->getUserClientCertificate();
        }

        return null;
    }

    public function getClientCertificateData(): ?string
    {
        if ($this->context) {
            return $this->context->getUserClientCertificateData();
        }

        return null;
    }

    public function getUsername(): ?string
    {
        if ($this->context) {
            return $this->context->getUserUsername();
        }

        return $this->options->getUsername();
    }

    public function getPassword(): ?string
    {
        if ($this->context) {
            return $this->context->getUserPassword();
        }

        return $this->options->getPassword();
    }

    public function getToken(): ?string
    {
        if ($this->context) {
            return $this->context->getUserToken();
        }

        return $this->options->getToken();
    }

    public function getServer(): string
    {
        if ($this->context) {
            return $this->context->getServer();
        }

        return $this->options->getEndpoint();
    }

    public function getServerCertificateAuthority(): ?string
    {
        if ($this->context) {
            return $this->context->getServerCertificateAuthority();
        }

        return null;
    }

    public function getServerCertificateAuthorityData(): ?string
    {
        if ($this->context) {
            return $this->context->getServerCertificateAuthorityData();
        }

        return null;
    }

    public function getNamespace(): string
    {
        if ($this->context && $this->context->getNamespace()) {
            return $this->context->getNamespace();
        }

        return $this->options->getNamespace();
    }
}
