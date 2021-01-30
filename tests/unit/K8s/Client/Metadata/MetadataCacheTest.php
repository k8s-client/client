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

namespace unit\K8s\Client\Metadata;

use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Metadata\MetadataParser;
use K8s\Client\Metadata\ModelMetadata;
use Psr\SimpleCache\CacheInterface;
use unit\K8s\Client\TestCase;

class MetadataCacheTest extends TestCase
{
    /**
     * @var MetadataParser|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $parser;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|CacheInterface
     */
    private $cache;

    /**
     * @var MetadataCache
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->parser = \Mockery::spy(MetadataParser::class);
        $this->cache = \Mockery::spy(CacheInterface::class);
        $this->subject = new MetadataCache(
            $this->parser,
            $this->cache
        );
    }

    public function testGetFromCacheWhenItIsntCached(): void
    {
        $this->cache->shouldReceive('get')
            ->andReturnNull();

        $this->subject->get(Pod::class);
        $this->parser->shouldHaveReceived('parse');
        $this->cache->shouldHaveReceived('set');
    }

    public function testGetFromCacheWhenItIsCached(): void
    {
        $this->cache->shouldReceive('get')
            ->andReturn(\Mockery::spy(ModelMetadata::class));

        $this->subject->get(Pod::class);
        $this->parser->shouldNotHaveReceived('parse');
        $this->cache->shouldNotHaveReceived('set');
    }

    public function testGetModelFqcnFromKind(): void
    {
        $result = $this->subject->getModelFqcnFromKind('v1', 'Pod');

        $this->assertEquals(Pod::class, $result);
    }
}
