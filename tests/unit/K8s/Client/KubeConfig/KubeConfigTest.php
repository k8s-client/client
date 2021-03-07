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

namespace unit\K8s\Client\KubeConfig;

use K8s\Client\KubeConfig\KubeConfig;
use K8s\Client\KubeConfig\Model\Cluster;
use K8s\Client\KubeConfig\Model\Context;
use K8s\Client\KubeConfig\Model\FullContext;
use K8s\Client\KubeConfig\Model\User;
use unit\K8s\Client\TestCase;

class KubeConfigTest extends TestCase
{
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new KubeConfig(
            ['current-context' => 'minikube'],
            [
                new Cluster(['name' => 'cluster1']),
            ],
            [
                new Context(['name' => 'minikube', 'context' => ['cluster' => 'cluster1', 'user' => 'user1']]),
            ],
            [
                new User(['name' => 'user1'])
            ]
        );
    }

    public function testGetClusters(): void
    {
        $result = $this->subject->getClusters();

        $this->assertCount(1, $result);
        $this->assertEquals('cluster1', $result[0]->getName());
    }

    public function testGetContexts(): void
    {
        $result = $this->subject->getContexts();

        $this->assertCount(1, $result);
        $this->assertEquals('minikube', $result[0]->getName());
    }

    public function testGetUsers(): void
    {
        $result = $this->subject->getUsers();

        $this->assertCount(1, $result);
        $this->assertEquals('user1', $result[0]->getName());
    }

    public function testGetUser(): void
    {
        $result = $this->subject->getUser('user1');

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('user1', $result->getName());
    }

    public function testGetCluster(): void
    {
        $result = $this->subject->getCluster('cluster1');

        $this->assertInstanceOf(Cluster::class, $result);
        $this->assertEquals('cluster1', $result->getName());
    }

    public function testGetContext(): void
    {
        $result = $this->subject->getContext('minikube');

        $this->assertInstanceOf(Context::class, $result);
        $this->assertEquals('minikube', $result->getName());
    }

    public function testGetDefaultFullContext(): void
    {
        $result = $this->subject->getFullContext();

        $this->assertInstanceOf(FullContext::class, $result);
    }
}
