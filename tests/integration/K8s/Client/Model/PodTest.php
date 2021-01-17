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

namespace integration\K8s\Client\Model;

use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Api\Model\Api\Core\v1\PodList;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\WatchEvent;

class PodTest extends TestCase
{
    public function testItCanCreatePods(): void
    {
        $pod = new Pod(
            'test-pod',
            [new Container('test-pod', 'nginx:latest')]
        );

        /** @var Pod $newPod */
        $newPod = $this->k8s()->create($pod);

        $this->assertInstanceOf(Pod::class, $newPod);
        $this->assertEquals('test-pod', $newPod->getName());
        $this->assertCount(1, $newPod->getContainers());
        $this->assertEquals('nginx:latest', $pod->getContainers()[0]->getImage());
    }

    public function testItCanReadPods(): void
    {
        /** @var Pod $newPod */
        $pod = $this->k8s()->read('test-pod', Pod::class);

        $this->assertInstanceOf(Pod::class, $pod);
        $this->assertEquals('test-pod', $pod->getName());
    }

    public function testItCanListPods(): void
    {
        /** @var PodList $podList */
        $podList = $this->k8s()->listAll(Pod::class);

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
        $podList = $this->k8s()->listNamespaced(Pod::class);

        $this->assertInstanceOf(PodList::class, $podList);
        $this->assertGreaterThanOrEqual(1, count($podList->getItems()));
        foreach ($podList as $pod) {
            $this->assertInstanceOf(Pod::class, $pod);
            $this->assertNotEmpty($pod->getName());
        }
    }

    public function testItCanWatchNamespacedPods(): void
    {
        $this->createPods('test', 5);
        $this->waitForKind(Pod::class, 5);

        $results = [];
        $this->k8s()->watchNamespaced(function (WatchEvent $event) use (&$results) {
            $results[] = $event;
            if (count($results) === 5) {
                return false;
            }
        }, Pod::class);

        $this->assertCount(5, $results);
        /** @var WatchEvent $result */
        foreach ($results as $result) {
            $this->assertInstanceOf(Pod::class, $result->getObject());
        }
    }

    public function testItCanWatchAllPods(): void
    {
        $results = [];

        $this->k8s()->watchAll(function (WatchEvent $event) use (&$results) {
            $results[] = $event;
            if (count($results) === 5) {
                return false;
            }
        }, Pod::class);

        $this->assertGreaterThanOrEqual(5, $results);
        /** @var WatchEvent $result */
        foreach ($results as $result) {
            $this->assertInstanceOf(Pod::class, $result->getObject());
        }
    }

    public function testItCanDeletePods(): void
    {
        /** @var Pod $deleted */
        $pod = $this->k8s()->read('test-pod', Pod::class);
        $deleted = $this->k8s()->delete($pod);

        $this->assertInstanceOf(Pod::class, $deleted);
        $this->assertEquals('test-pod', $deleted->getName());
        $this->assertNotNull($deleted->getDeletionTimestamp());
    }

    public function testItCanDeleteNamespacedPods(): void
    {
        $this->k8s()->deleteAllNamespaced(Pod::class);

        /** @var Pod $pod */
        foreach ($this->k8s()->listNamespaced(Pod::class) as $pod) {
            $this->assertNotNull($pod->getDeletionTimestamp());
        }
    }
}
