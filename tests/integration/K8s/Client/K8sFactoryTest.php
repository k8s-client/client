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

namespace integration\K8s\Client;

use K8s\Client\K8s;
use K8s\Client\K8sFactory;
use unit\K8s\Client\TestCase;

class K8sFactoryTest extends TestCase
{
    /**
     * @var K8sFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new K8sFactory();
    }

    public function testItCanLoadFromTheKubeConfig(): void
    {
        $_SERVER['HOME'] = __DIR__ . '/../../../resources';

        $result = $this->subject->loadFromKubeConfig();
        $options = $result->getOptions();
        $kubeConfig = $options->getKubeConfigContext();

        $this->assertInstanceOf(K8s::class, $result);
        $this->assertEquals('https://127.0.0.1:8443', $options->getEndpoint());
        $this->assertEquals('default', $kubeConfig->getNamespace());
        $this->assertEquals('/home/user/.minikube/ca.crt', $kubeConfig->getServerCertificateAuthority());
        $this->assertEquals('/home/user/.minikube/profiles/minikube/client.crt', $kubeConfig->getUserClientCertificate());
        $this->assertEquals('/home/user/.minikube/profiles/minikube/client.key', $kubeConfig->getUserClientKey());
    }

    public function testItCanLoadFromTheKubeConfigData(): void
    {
        $config =file_get_contents(__DIR__ . '/../../../resources/.kube/config');

        $result = $this->subject->loadFromKubeConfigData($config);
        $options = $result->getOptions();
        $kubeConfig = $options->getKubeConfigContext();

        $this->assertInstanceOf(K8s::class, $result);
        $this->assertEquals('https://127.0.0.1:8443', $options->getEndpoint());
        $this->assertEquals('default', $kubeConfig->getNamespace());
        $this->assertEquals('/home/user/.minikube/ca.crt', $kubeConfig->getServerCertificateAuthority());
        $this->assertEquals('/home/user/.minikube/profiles/minikube/client.crt', $kubeConfig->getUserClientCertificate());
        $this->assertEquals('/home/user/.minikube/profiles/minikube/client.key', $kubeConfig->getUserClientKey());
    }
}
