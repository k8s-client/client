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

use Http\Discovery\Psr17FactoryDiscovery;
use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Api\Model\Api\Core\v1\PodList;
use K8s\Api\Model\Api\Policy\v1beta1\Eviction;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\WatchEvent;
use K8s\Client\Patch\JsonPatch;

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

    public function testItCanReplacePodData(): void
    {
        /** @var Pod $pod */
        $pod = $this->k8s()->read('test-pod', Pod::class);
        $pod->getContainers()[0]->setImage('nginx:1.18');
        $pod->setLabels(['foo' => 'bar']);

        $pod = $this->k8s()->replace($pod);
        $this->assertEquals(['foo' => 'bar'], $pod->getLabels());
    }

    public function testItCanReadPodStatus(): void
    {
        /** @var Pod $pod */
        $pod = $this->k8s()->readStatus('test-pod', Pod::class);

        $this->assertInstanceOf(Pod::class, $pod);
        $this->assertEquals('test-pod', $pod->getName());
    }

    public function testItCanReplacePodStatus(): void
    {
        /** @var Pod $pod */
        $pod = $this->k8s()->readStatus('test-pod', Pod::class);
        $pod->getStatus()->setQosClass('Guaranteed');
        $pod = $this->k8s()->replaceStatus($pod);

        $this->assertInstanceOf(Pod::class, $pod);
        $this->assertEquals('Guaranteed', $pod->getQosClass());
    }

    public function testItCanPatchPodStatus(): void
    {
        /** @var Pod $pod */
        $pod = $this->k8s()->readStatus('test-pod', Pod::class);

        $patch = new JsonPatch();
        $patch->replace('/status/qosClass', 'BestEffort');

        $pod = $this->k8s()->patchStatus($pod, $patch);

        $this->assertInstanceOf(Pod::class, $pod);
        $this->assertEquals('BestEffort', $pod->getQosClass());
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

    public function testItCanProxyHttpToThePod(): void
    {
        $this->createAndWaitForPod(new Pod(
            'proxy-test',
            [new Container('proxy-test', 'nginx:latest')]
        ));
        $pod = $this->k8s()->read('proxy-test', Pod::class);

        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $result = $this->k8s()->proxy(
            $pod,
            $requestFactory->createRequest('GET', '/'))
        ;

        $this->assertStringContainsString('Welcome to nginx', (string)$result->getBody());
    }

    public function testItCanEvictThePod(): void
    {
        $this->createAndWaitForPod(new Pod(
            'eviction-test',
            [new Container('eviction-test', 'nginx:latest')]
        ));

        $result = $this->k8s()->create(new Eviction('eviction-test'));
        $this->assertInstanceOf(Eviction::class, $result);
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
