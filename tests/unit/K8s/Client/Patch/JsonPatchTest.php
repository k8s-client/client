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

namespace unit\K8s\Client\Patch;

use K8s\Client\Patch\JsonPatch;
use K8s\Client\Patch\Operation\Add;
use K8s\Client\Patch\Operation\Copy;
use K8s\Client\Patch\Operation\Move;
use K8s\Client\Patch\Operation\Remove;
use K8s\Client\Patch\Operation\Replace;
use K8s\Client\Patch\Operation\Test;
use unit\K8s\Client\TestCase;

class JsonPatchTest extends TestCase
{
    /**
     * @var JsonPatch
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new JsonPatch();
    }

    public function testItAddsAnAddOperation(): void
    {
        $this->subject->add('/foo', 'bar');

        $result = $this->subject->getOperations()[0];
        $this->assertInstanceOf(Add::class, $result);
        $this->assertEquals('/foo', $result->getPath());
        $this->assertEquals('bar', $result->getValue());
    }

    public function testItAddsAnRemoveOperation(): void
    {
        $this->subject->remove('/foo');

        $result = $this->subject->getOperations()[0];
        $this->assertInstanceOf(Remove::class, $result);
        $this->assertEquals('/foo', $result->getPath());
    }

    public function testItAddsAnReplaceOperation(): void
    {
        $this->subject->replace('/foo', 'bar');

        $result = $this->subject->getOperations()[0];
        $this->assertInstanceOf(Replace::class, $result);
        $this->assertEquals('/foo', $result->getPath());
        $this->assertEquals('bar', $result->getValue());
    }

    public function testItAddsAnCopyOperation(): void
    {
        $this->subject->copy('/foo', '/bar');

        $result = $this->subject->getOperations()[0];
        $this->assertInstanceOf(Copy::class, $result);
        $this->assertEquals('/bar', $result->getPath());
        $this->assertEquals('/foo', $result->getFrom());
    }

    public function testItAddsAnMoveOperation(): void
    {
        $this->subject->move('/foo', '/bar');

        $result = $this->subject->getOperations()[0];
        $this->assertInstanceOf(Move::class, $result);
        $this->assertEquals('/bar', $result->getPath());
        $this->assertEquals('/foo', $result->getFrom());
    }

    public function testItAddsAnTestOperation(): void
    {
        $this->subject->test('/foo', 'bar');

        $result = $this->subject->getOperations()[0];
        $this->assertInstanceOf(Test::class, $result);
        $this->assertEquals('/foo', $result->getPath());
        $this->assertEquals('bar', $result->getValue());
    }

    public function testItHasTheCorrectContentType(): void
    {
        $this->assertEquals('application/json-patch+json', $this->subject->getContentType());
    }

    public function testItReturnsTheArrayRepresentation(): void
    {
        $this->subject->add('/foo', 'bar');
        $this->subject->remove('/bar');

        $result = $this->subject->toArray();
        $this->assertEquals([
            [
                'op' => 'add',
                'path' => '/foo',
                'value' => 'bar',
            ],
            [
                'op' => 'remove',
                'path' => '/bar',
            ],
        ], $result);
    }
}
