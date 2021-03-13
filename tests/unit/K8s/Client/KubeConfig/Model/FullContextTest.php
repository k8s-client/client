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

namespace unit\K8s\Client\KubeConfig\Model;

use K8s\Client\KubeConfig\Model\Cluster;
use K8s\Client\KubeConfig\Model\Context;
use K8s\Client\KubeConfig\Model\FullContext;
use K8s\Client\KubeConfig\Model\User;
use unit\K8s\Client\TestCase;

class FullContextTest extends TestCase
{
    /**
     * @var FullContext
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new FullContext(
            new Context([
                'name' => 'minikube',
                'context' => [
                    'cluster' => 'cluster1',
                    'user' => 'user1',
                    'namespace' => 'namespace',
                ]]),
            new Cluster([
                'name' => 'cluster1',
                'cluster' => [
                    'server' => 'server',
                    'certificate-authority' => 'ca',
                ],
            ]),
            new User([
                'name' => 'user1',
                'user' => [
                    'client-certificate' => 'cert',
                    'client-certificate-data' => 'cert-data',
                    'client-key' => 'key',
                    'client-key-data' => 'key-data',
                    'token' => 'token',
                    'token-file' => 'file',
                    'username' => 'username',
                    'password' => 'password',
                ],
            ])
        );
    }

    public function testGetUserClientCertificate(): void
    {
        $this->assertEquals('cert', $this->subject->getUserClientCertificate());
    }

    public function testGetUserClientKey(): void
    {
        $this->assertEquals('key', $this->subject->getUserClientKey());
    }

    public function testGetUserToken(): void
    {
        $this->assertEquals('token', $this->subject->getUserToken());
    }

    public function testGetUserTokenFile(): void
    {
        $this->assertEquals('file', $this->subject->getUserTokenFile());
    }

    public function testGetUserUsername(): void
    {
        $this->assertEquals('username', $this->subject->getUserUsername());
    }

    public function testGetUserPassword(): void
    {
        $this->assertEquals('password', $this->subject->getUserPassword());
    }

    public function testGetServerCertificateAuthority(): void
    {
        $this->assertEquals('ca', $this->subject->getServerCertificateAuthority());
    }

    public function testGetServer(): void
    {
        $this->assertEquals('server', $this->subject->getServer());
    }

    public function testGetNamespace(): void
    {
        $this->assertEquals('namespace', $this->subject->getNamespace());
    }

    public function testGetAuthType(): void
    {
        $this->assertEquals('certificate', $this->subject->getAuthType());
    }

    public function testGetUserClientCertificateData(): void
    {
        $this->assertEquals('cert-data', $this->subject->getUserClientCertificateData());
    }

    public function testGetUserClientKeyData(): void
    {
        $this->assertEquals('key-data', $this->subject->getUserClientKeyData());
    }
}
