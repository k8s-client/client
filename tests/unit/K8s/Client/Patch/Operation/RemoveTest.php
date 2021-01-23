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

use K8s\Client\Patch\Operation\Remove;
use unit\K8s\Client\TestCase;

class RemoveTest extends TestCase
{
    /**
     * @var Remove
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Remove('/bar');
    }

    public function testItHasTheRightOp(): void
    {
        $this->assertEquals('remove', $this->subject->getOp());
    }

    public function testGetPath(): void
    {
        $this->assertEquals('/bar', $this->subject->getPath());
    }

    public function testToArray(): void
    {
        $expected = [
            'op' => 'remove',
            'path' => '/bar',
        ];

        $this->assertEquals($expected, $this->subject->toArray());
    }
}