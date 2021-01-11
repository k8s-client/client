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
use K8s\Client\Metadata\ModelPropertyMetadata;
use K8s\Core\Annotation\Attribute;
use unit\K8s\Client\TestCase;

class ModelPropertyMetadataTest extends TestCase
{
    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var ModelPropertyMetadata
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->attribute = new Attribute();
        $this->attribute->name = 'bar';
        $this->attribute->type = 'model';
        $this->attribute->model = Pod::class;
        $this->subject = new ModelPropertyMetadata(
            'foo',
            $this->attribute
        );
    }

    public function testGetPropertyName(): void
    {
        $this->assertEquals('foo', $this->subject->getName());
    }

    public function testGetAttributeName(): void
    {
        $this->assertEquals('bar', $this->subject->getAttributeName());
    }

    public function testTypeIsModel(): void
    {
        $this->assertTrue($this->subject->isModel());
    }

    public function testTypeIsCollection(): void
    {
        $this->attribute->type = 'collection';

        $this->assertTrue($this->subject->isCollection());
    }

    public function testTypeIsDateTime(): void
    {
        $this->attribute->type = 'datetime';

        $this->assertTrue($this->subject->isDateTime());
    }
}
