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

namespace unit\K8s\Client\Patch\Operation;

use K8s\Client\Patch\Operation\Copy;
use unit\K8s\Client\TestCase;

class CopyTest extends TestCase
{
    /**
     * @var Copy
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Copy('/foo', '/bar');
    }

    public function testItHasTheRightOp(): void
    {
        $this->assertEquals('copy', $this->subject->getOp());
    }

    public function testSetFrom(): void
    {
        $this->subject->setFrom('/meh');

        $this->assertEquals('/meh', $this->subject->getFrom());
    }

    public function testGetFrom(): void
    {
        $this->assertEquals('/foo', $this->subject->getFrom());
    }

    public function testGetPath(): void
    {
        $this->assertEquals('/bar', $this->subject->getPath());
    }

    public function testToArray(): void
    {
        $expected = [
            'op' => 'copy',
            'path' => '/bar',
            'from' => '/foo',
        ];

        $this->assertEquals($expected, $this->subject->toArray());
    }
}
