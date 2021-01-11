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
use K8s\Client\Exception\RuntimeException;
use K8s\Client\Metadata\KindMetadata;
use K8s\Client\Metadata\ModelMetadata;
use K8s\Client\Metadata\ModelPropertyMetadata;
use K8s\Client\Metadata\OperationMetadata;
use unit\K8s\Client\TestCase;

class ModelMetadataTest extends TestCase
{
    /**
     * @var array
     */
    private $properties;

    /**
     * @var array
     */
    private $operations;

    /**
     * @var KindMetadata|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $kind;

    /**
     * @var ModelMetadata
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->properties = [\Mockery::spy(ModelPropertyMetadata::class)];
        $this->operations = [\Mockery::spy(OperationMetadata::class)];
        $this->kind = \Mockery::spy(KindMetadata::class);
        $this->subject = new ModelMetadata(
            Pod::class,
            $this->properties,
            $this->operations,
            $this->kind
        );
    }

    public function testGetModelFqcn(): void
    {
        $this->assertEquals(Pod::class, $this->subject->getModelFqcn());
    }

    public function testGetOperations(): void
    {
        $this->assertEquals($this->operations, $this->subject->getOperations());
    }

    public function testGetProperties(): void
    {
        $this->assertEquals($this->properties, $this->subject->getProperties());
    }

    public function testGetKind(): void
    {
        $this->assertEquals($this->kind, $this->subject->getKind());
    }

    public function testGetOperationByType(): void
    {
        $this->operations[0]->shouldReceive('getType')->andReturn('list');

        $result = $this->subject->getOperationByType('list');
        $this->assertEquals($this->operations, [$result]);
    }

    public function testGetOperationByTypeThrowsExceptionWhenNotFound(): void
    {
        $this->operations[0]->shouldReceive('getType')->andReturn('foo');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^The operation type "list" is not recognised/');
        $this->subject->getOperationByType('list');
    }
}
