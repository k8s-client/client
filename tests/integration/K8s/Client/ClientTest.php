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

use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;

class ClientTest extends TestCase
{
    public function testItCanCreatePods(): void
    {
        $pod = new Pod(
            'test-pod',
            [new Container('test-pod', 'nginx:latest')]
        );

        /** @var Pod $newPod */
        $newPod = $this->client->create($pod);

        $this->assertInstanceOf(Pod::class, $newPod);
        $this->assertEquals('test-pod', $newPod->getName());
        $this->assertCount(1, $newPod->getContainers());
        $this->assertEquals('nginx:latest', $pod->getContainers()[0]->getImage());
    }
}
