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

use Doctrine\Common\Annotations\AnnotationReader;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\Metadata\MetadataParser;
use K8s\Client\Metadata\ModelPropertyMetadata;
use K8s\Client\Metadata\OperationMetadata;
use unit\K8s\Client\TestCase;

class MetadataParserTest extends TestCase
{
    /**
     * @var MetadataParser
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new MetadataParser(new AnnotationReader());
    }

    public function testItParsesThePodMetadata(): void
    {
        $result = $this->subject->parse(Pod::class);

        $this->assertEquals('Pod', $result->getKind()->getKind());
        $this->assertEquals('v1', $result->getKind()->getVersion());

        $this->assertNotEmpty($result->getOperations());
        foreach ($result->getOperations() as $operation) {
            $this->assertInstanceOf(OperationMetadata::class, $operation);
        }
        $this->assertNotEmpty($result->getProperties());
        foreach ($result->getProperties() as $property) {
            $this->assertInstanceOf(ModelPropertyMetadata::class, $property);
        }
        $this->assertEquals(Pod::class, $result->getModelFqcn());
    }

    public function testItParsesClassesThatExtendTheBase(): void
    {
        $model = new class extends Pod {
            public function __construct(?string $name = null, iterable $containers = [])
            {
                parent::__construct($name, $containers);
            }
        };
        $result = $this->subject->parse(get_class($model));

        $this->assertEquals('Pod', $result->getKind()->getKind());
        $this->assertEquals('v1', $result->getKind()->getVersion());

        $this->assertNotEmpty($result->getOperations());
        foreach ($result->getOperations() as $operation) {
            $this->assertInstanceOf(OperationMetadata::class, $operation);
        }
        $this->assertNotEmpty($result->getProperties());
        foreach ($result->getProperties() as $property) {
            $this->assertInstanceOf(ModelPropertyMetadata::class, $property);
        }
        $this->assertEquals(get_class($model), $result->getModelFqcn());
    }
}
