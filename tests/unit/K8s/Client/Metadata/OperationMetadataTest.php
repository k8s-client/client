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
use K8s\Client\Metadata\OperationMetadata;
use K8s\Core\Annotation\Operation;
use unit\K8s\Client\TestCase;

class OperationMetadataTest extends TestCase
{
    /**
     * @var Operation
     */
    private $operation;

    /**
     * @var OperationMetadata
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->operation = new Operation();
        $this->operation->type = 'list';
        $this->operation->path = '/foo';
        $this->operation->response = 'static::class';
        $this->operation->body = Pod::class;
        $this->subject = new OperationMetadata($this->operation);
    }

    public function testGetPath(): void
    {
        $this->assertEquals('/foo', $this->subject->getPath());
    }

    public function testGetType(): void
    {
        $this->assertEquals('list', $this->subject->getType());
    }

    public function testGetResponseIsNullWhenItIsSelf(): void
    {
        $this->assertEquals(null, $this->subject->getResponseFqcn());
    }

    public function testResponseIsSelfWhenItIsStatic(): void
    {
        $this->assertTrue($this->subject->isResponseSelf());
    }

    public function testGetResponseIsNotNullWhenItIsNotSelf(): void
    {
        $this->operation->response = Pod::class;

        $this->assertEquals(Pod::class, $this->subject->getResponseFqcn());
    }

    public function testResponseIsNotSelfWhenItIsNotStatic(): void
    {
        $this->operation->response = Pod::class;

        $this->assertFalse($this->subject->isResponseSelf());
    }

    public function testIsBodyRequiredWhenItIs(): void
    {
        $this->assertTrue($this->subject->isBodyRequired());
    }

    public function testIsBodyRequiredWhenItIsNot(): void
    {
        $this->operation->body = null;

        $this->assertFalse($this->subject->isBodyRequired());
    }
}
