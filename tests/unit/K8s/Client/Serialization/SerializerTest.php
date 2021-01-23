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
use K8s\Client\Serialization\ModelDenormalizer;
use K8s\Client\Serialization\ModelNormalizer;
use K8s\Client\Serialization\Serializer;
use unit\K8s\Client\TestCase;

class SerializerTest extends TestCase
{
    /**
     * @var ModelNormalizer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $normalizer;

    /**
     * @var ModelDenormalizer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $denormalizer;

    /**
     * @var Serializer
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->normalizer = \Mockery::spy(ModelNormalizer::class);
        $this->denormalizer = \Mockery::spy(ModelDenormalizer::class);
        $this->subject = new Serializer(
            $this->normalizer,
            $this->denormalizer
        );
    }

    public function testSerializeWithArray(): void
    {
        $result = $this->subject->serialize(['foo' => 'bar']);

        $this->assertEquals('{"foo":"bar"}', $result);
        $this->normalizer->shouldNotHaveReceived('normalize');
    }

    public function testSerializeWithObject(): void
    {
        $this->normalizer->shouldReceive('normalize')
            ->andReturn(['pod' => 'data']);
        $result = $this->subject->serialize(new Pod(null, []));

        $this->assertEquals('{"pod":"data"}', $result);
    }

    public function testDeserialize(): void
    {
        $pod = new Pod(null, []);
        $this->denormalizer->shouldReceive('denormalize')
            ->with(['pod' => 'data'], Pod::class)
            ->andReturn($pod);

        $result = $this->subject->deserialize('{"pod":"data"}', Pod::class);
        $this->assertEquals($pod, $result);
    }
}
