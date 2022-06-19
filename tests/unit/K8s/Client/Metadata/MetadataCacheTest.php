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
use Mockery;
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
        $this->parser = Mockery::spy(MetadataParser::class);
        $this->cache = Mockery::spy(CacheInterface::class);
        $this->subject = new MetadataCache(
            $this->cache,
            $this->parser
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
            ->andReturn(Mockery::spy(ModelMetadata::class));

        $this->subject->get(Pod::class);
        $this->parser->shouldNotHaveReceived('parse');
        $this->cache->shouldNotHaveReceived('set');
    }

    public function testGetModelFqcnFromKind(): void
    {
        $result = $this->subject->getModelFqcnFromKind('v1', 'Pod');

        $this->assertEquals(Pod::class, $result);
    }

    public function testCacheAllCachesTheKindMapAndMetadata(): void
    {
        $this->subject->cacheAll();

        $this->cache->shouldHaveReceived(
            'set',
            [
                Mockery::pattern('/^kind-meta/'),
                Mockery::andAnyOtherArgs(),
            ]
        );
        $this->cache->shouldHaveReceived(
            'set',
            [
                'kind-map',
                Mockery::andAnyOtherArgs(),
            ]
        );
    }

    public function testDeleteAllRemovesTheKindMapAndMetadataCache(): void
    {
        $this->subject->deleteAll();

        $this->cache->shouldHaveReceived(
            'delete',
            ['kind-map']
        );
        $this->cache->shouldHaveReceived(
            'deleteMultiple',
            [
                Mockery::on(function (iterable $keys) {
                    foreach ($keys as $key) {
                        if (preg_match('/^kind-meta/', $key) !== 1) {
                            return false;
                        }
                    }

                    return true;
                }),
                Mockery::andAnyOtherArgs(),
            ]
        );
    }

    public function testIfThereIsNoCacheThenCacheAllDoesNotCache(): void
    {
        $this->subject = new MetadataCache(
            null,
            $this->parser
        );

        $this->subject->cacheAll();
        $this->expectNotToPerformAssertions();
    }

    public function testIfThereIsNoCacheThenDeleteAllDoesNothing(): void
    {
        $this->subject = new MetadataCache(
            null,
            $this->parser
        );

        $this->subject->deleteAll();
        $this->expectNotToPerformAssertions();
    }
}
