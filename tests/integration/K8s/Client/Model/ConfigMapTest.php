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
use K8s\Api\Model\Api\Core\v1\ConfigMap;
use K8s\Api\Model\Api\Core\v1\ConfigMapList;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;

class ConfigMapTest extends TestCase
{
    public function testItCanCreateConfigMaps(): void
    {
        $configMap = new ConfigMap('data');
        $configMap->setData([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $created = $this->k8s()->create($configMap);

        $this->assertInstanceOf(ConfigMap::class, $created);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $created->getData());
    }

    public function testItCanReadConfigMaps(): void
    {
        $configMap = $this->k8s()->read('data', ConfigMap::class);

        $this->assertInstanceOf(ConfigMap::class, $configMap);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $configMap->getData());
    }

    public function testItCanListNamespacedConfigMaps(): void
    {
        $result = $this->k8s()->listNamespaced(ConfigMap::class);

        $this->assertInstanceOf(ConfigMapList::class, $result);
        $this->assertGreaterThan(0 , count($result->getItems()));
        $this->assertInstanceOf(ConfigMap::class, $result->getItems()[0]);
    }

    public function testItCanDeleteConfigMaps(): void
    {
        $configMap = $this->k8s()->read('data', ConfigMap::class);
        $result = $this->k8s()->delete($configMap);

        $this->assertInstanceOf(Status::class, $result);
    }
}
