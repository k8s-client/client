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

use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Serialization\ModelNormalizer;
use unit\K8s\Client\TestCase;

class ModelNormalizerTest extends TestCase
{
    /**
     * @var MetadataCache
     */
    private $cache;

    /**
     * @var ModelNormalizer
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new MetadataCache();
        $this->subject = new ModelNormalizer($this->cache);
    }

    public function testNormalize(): void
    {
        $pod = new Pod(
            'foo',
            [new Container(
                'web', 'nginx:latest'
            )]
        );

        $result = $this->subject->normalize(
            $pod,
            Pod::class
        );
        $this->assertEquals(
            [
                'apiVersion' => 'v1',
                'kind' => 'Pod',
                'metadata' => (object)[
                    'name' => 'foo',
                ],
                'spec' => (object)[
                    'containers' => [
                        (object)[
                            'image' => 'nginx:latest',
                            'name' => 'web',
                        ],
                    ]
                ],
            ],
            $result
        );
    }
}
