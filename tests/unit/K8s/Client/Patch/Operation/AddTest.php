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

use K8s\Client\Patch\Operation\Add;
use unit\K8s\Client\TestCase;

class AddTest extends TestCase
{
    /**
     * @var Add
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Add('/foo', 'bar');
    }

    public function testItHasTheRightOp(): void
    {
        $this->assertEquals('add', $this->subject->getOp());
    }

    public function testGetValue(): void
    {
        $this->assertEquals('bar', $this->subject->getValue());
    }

    public function testSetValue(): void
    {
        $this->subject->setValue(true);

        $this->assertEquals(true, $this->subject->getValue());
    }

    public function testGetPath(): void
    {
        $this->subject->setPath('/bar');

        $this->assertEquals('/bar', $this->subject->getPath());
    }

    public function testToArray(): void
    {
        $expected = [
            'op' => 'add',
            'path' => '/foo',
            'value' => 'bar',
        ];

        $this->assertEquals($expected, $this->subject->toArray());
    }
}
