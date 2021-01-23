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
use K8s\Api\Model\Api\Apps\v1\Deployment;
use K8s\Api\Model\Api\Apps\v1\DeploymentList;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\PodTemplateSpec;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\LabelSelector;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\WatchEvent;
use K8s\Client\Patch\JsonPatch;

class DeploymentTest extends TestCase
{
    public function testItCanCreateDeployments(): void
    {
        $template = new PodTemplateSpec(
            'test-deployment',
            [new Container('test-deployment', 'nginx:latest')]
        );
        $template->setLabels(['app' => 'test-deployment']);

        $deployment = new Deployment(
            'test-deployment',
            new LabelSelector([], ['app' => 'test-deployment']),
            $template
        );
        $deployment->setReplicas(2);

        $deployment = $this->k8s()->create($deployment);

        $this->assertInstanceOf(Deployment::class, $deployment);
        $this->assertEquals('test-deployment', $deployment->getName());
    }

    public function testItCanReadDeployments(): void
    {
        /** @var Deployment $deployment */
        $deployment = $this->k8s()->read('test-deployment', Deployment::class);

        $this->assertInstanceOf(Deployment::class, $deployment);
        $this->assertEquals('test-deployment', $deployment->getName());
    }

    public function testItCanPatchDeployments(): void
    {
        /** @var Deployment $deployment */
        $deployment = $this->k8s()->read('test-deployment', Deployment::class);

        $patch = new JsonPatch();
        $patch->replace('/spec/replicas', 1);
        $patch->add('/metadata/labels', ['test' => 'patch']);

        $deployment = $this->k8s()->patch($deployment, $patch);

        $this->assertInstanceOf(Deployment::class, $deployment);
        $this->assertEquals('test-deployment', $deployment->getName());
        $this->assertEquals(['test' => 'patch'], $deployment->getLabels());
    }

    public function testItCanListDeployments(): void
    {
        /** @var DeploymentList $deploymentList */
        $deploymentList = $this->k8s()->listAll(Deployment::class);

        $this->assertInstanceOf(DeploymentList::class, $deploymentList);
        $this->assertGreaterThan(0, count($deploymentList->getItems()));
        foreach ($deploymentList as $deployment) {
            $this->assertInstanceOf(Deployment::class, $deployment);
            $this->assertNotEmpty($deployment->getName());
        }
    }

    public function testItCanListNamespacedDeployments(): void
    {
        /** @var DeploymentList $deploymentList */
        $deploymentList = $this->k8s()->listNamespaced(Deployment::class);

        $this->assertInstanceOf(DeploymentList::class, $deploymentList);
        $this->assertGreaterThanOrEqual(1, count($deploymentList->getItems()));
        foreach ($deploymentList as $deployment) {
            $this->assertInstanceOf(Deployment::class, $deployment);
            $this->assertNotEmpty($deployment->getName());
        }
    }

    public function testItCanWatchNamespacedDeployments(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $template = new PodTemplateSpec(
                "test-deployment-$i",
                [new Container("test-deployment-$i", 'nginx:latest')]
            );
            $template->setLabels(['app' => "test-deployment-$i"]);
            $deployment = new Deployment(
                "test-deployment-$i",
                new LabelSelector([], ['app' => "test-deployment-$i"]),
                $template
            );
            $this->client->create($deployment);
        }

        $results = [];

        $this->k8s()->watchNamespaced(function (WatchEvent $event) use (&$results) {
            $results[] = $event;

            return false;
        }, Deployment::class);

        $this->assertCount(1, $results);
        /** @var WatchEvent $result */
        foreach ($results as $result) {
            $this->assertInstanceOf(Deployment::class, $result->getObject());
        }
    }

    public function testItCanWatchAllDeployments(): void
    {
        $results = [];

        $this->k8s()->watchAll(function (WatchEvent $event) use (&$results) {
            $results[] = $event;

            return false;
        }, Deployment::class);

        $this->assertGreaterThanOrEqual(1, $results);
        /** @var WatchEvent $result */
        foreach ($results as $result) {
            $this->assertInstanceOf(Deployment::class, $result->getObject());
        }
    }

    public function testItCanDeleteDeployments(): void
    {
        /** @var Deployment $deployment */
        $deployment = $this->k8s()->read('test-deployment', Deployment::class);
        $deleted = $this->k8s()->delete($deployment);

        $this->assertInstanceOf(Status::class, $deleted);
    }

    public function testItCanDeleteNamespacedDeployments(): void
    {
        $status = $this->k8s()->deleteAllNamespaced(Deployment::class);

        $this->assertInstanceOf(Status::class, $status);
    }
}
