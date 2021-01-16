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

namespace integration\K8s\Client\Models;

use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Api\Model\Api\Core\v1\PodList;

class PodsTest extends TestCase
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

    public function testItCanReadPods(): void
    {
        /** @var Pod $newPod */
        $pod = $this->client->read('test-pod', Pod::class);

        $this->assertInstanceOf(Pod::class, $pod);
        $this->assertEquals('test-pod', $pod->getName());
    }

    public function testItCanListPods(): void
    {
        /** @var PodList $podList */
        $podList = $this->client->listAll(Pod::class);

        $this->assertInstanceOf(PodList::class, $podList);
        $this->assertGreaterThan(0, count($podList->getItems()));
        foreach ($podList as $pod) {
            $this->assertInstanceOf(Pod::class, $pod);
            $this->assertNotEmpty($pod->getName());
        }
    }

    public function testItCanListNamespacedPods(): void
    {
        /** @var PodList $podList */
        $podList = $this->client->listNamespaced(Pod::class);

        $this->assertInstanceOf(PodList::class, $podList);
        $this->assertCount(1, $podList->getItems());
        foreach ($podList as $pod) {
            $this->assertInstanceOf(Pod::class, $pod);
            $this->assertNotEmpty($pod->getName());
        }
    }

    public function testItCanDeletePods(): void
    {
        /** @var Pod $deleted */
        $pod = $this->client->read('test-pod', Pod::class);
        $deleted = $this->client->delete($pod);

        $this->assertInstanceOf(Pod::class, $deleted);
        $this->assertEquals('test-pod', $deleted->getName());
        $this->assertNotNull($deleted->getDeletionTimestamp());
    }

    public function testItCanDeleteNamespacedPods(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->client->create(new Pod(
                "test-pod-$i",
                [new Container('test-pod', 'nginx:latest')]
            ));
        }

        $this->client->deleteAllNamespaced(Pod::class);
        /** @var PodList $podList */
        $podList = $this->client->listNamespaced(Pod::class);

        $this->assertInstanceOf(PodList::class, $podList);
        $this->assertCount(0, $podList->getItems());
    }
}
