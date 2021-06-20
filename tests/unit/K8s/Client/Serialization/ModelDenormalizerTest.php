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

namespace unit\K8s\Client\Serialization;

use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Serialization\ModelDenormalizer;
use unit\K8s\Client\TestCase;

class ModelDenormalizerTest extends TestCase
{
    /**
     * @var MetadataCache
     */
    private $cache;

    /**
     * @var ModelDenormalizer
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new MetadataCache();
        $this->subject = new ModelDenormalizer($this->cache);
    }

    public function testItDenormalizes(): void
    {
        $data = [
            'apiVersion' => 'v1',
            'kind' => 'Pod',
            'metadata' => [
                'name' => 'foo',
            ],
            'spec' => [
                'containers' => [
                    [
                        'image' => 'nginx:latest',
                        'name' => 'web',
                    ],
                ]
            ],
        ];
        $result = $this->subject->denormalize(
            $data,
            Pod::class
        );

        $this->assertInstanceOf(Pod::class, $result);
        /** @var Pod $result */

        $this->assertEquals('foo', $result->getName());
        $containers = $result->getContainers();
        $this->assertCount(1, $containers);

        $first = $containers[0];
        $this->assertEquals('nginx:latest', $first->getImage());
        $this->assertEquals('web', $first->getName());
    }

    public function testItFindsTheModelWhenDenormalizing(): void
    {
        $data = [
            'apiVersion' => 'v1',
            'kind' => 'Pod',
            'metadata' => [
                'name' => 'foo',
            ],
            'spec' => [
                'containers' => [
                    [
                        'image' => 'nginx:latest',
                        'name' => 'web',
                    ],
                ]
            ],
        ];
        $result = $this->subject->denormalize($data);

        $this->assertInstanceOf(Pod::class, $result);
        /** @var Pod $result */

        $this->assertEquals('foo', $result->getName());
        $containers = $result->getContainers();
        $this->assertCount(1, $containers);

        $first = $containers[0];
        $this->assertEquals('nginx:latest', $first->getImage());
        $this->assertEquals('web', $first->getName());
    }
}
