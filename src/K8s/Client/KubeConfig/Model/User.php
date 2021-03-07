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

use K8s\Client\Exception\RuntimeException;

class User
{
    /**
     * @var array<string, mixed>
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getAuthType(): string
    {
        if ($this->getClientKey() !== null || $this->getClientKeyData() !== null) {
            return 'certificate';
        } elseif ($this->getExec() !== null) {
            return 'token';
        } elseif ($this->getToken() !== null || $this->getTokenFile() !== null) {
            return 'token';
        } elseif ($this->getUsername() !== null) {
            return 'basic';
        } else {
            throw new RuntimeException('Unable to determine the auth type defined for the user.');
        }
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getClientCertificate(): ?string
    {
        return $this->data['user']['client-certificate'] ?? null;
    }

    public function getClientCertificateData(): ?string
    {
        return $this->data['user']['client-certificate-data'] ?? null;
    }

    public function getClientKey(): ?string
    {
        return $this->data['user']['client-key'] ?? null;
    }

    public function getClientKeyData(): ?string
    {
        return $this->data['user']['client-key-data'] ?? null;
    }

    public function getUsername(): ?string
    {
        return $this->data['user']['username'] ?? null;
    }

    public function getPassword(): ?string
    {
        return $this->data['user']['password'] ?? null;
    }

    public function getToken(): ?string
    {
        return $this->data['user']['token'] ?? null;
    }

    public function getTokenFile(): ?string
    {
        return $this->data['user']['token-file'] ?? null;
    }

    public function getExec(): ?UserExec
    {
        return isset($this->data['user']['exec']) ? new UserExec($this->data['user']['exec']) : null;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
