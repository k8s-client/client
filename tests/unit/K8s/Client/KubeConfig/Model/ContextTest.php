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

namespace unit\K8s\Client\KubeConfig\Model;

use K8s\Client\KubeConfig\Model\Context;
use unit\K8s\Client\TestCase;

class ContextTest extends TestCase
{
    /**
     * @var Context
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Context([
            'name' => 'foo',
            'context' => [
                'namespace' => 'meh',
                'cluster' => 'bar',
                'user' => 'user1',
            ]
        ]);
    }

    public function testGetClusterName(): void
    {
        $this->assertEquals('bar', $this->subject->getClusterName());
    }

    public function testGetName(): void
    {
        $this->assertEquals('foo', $this->subject->getName());
    }

    public function testGetUserName(): void
    {
        $this->assertEquals('user1', $this->subject->getUserName());
    }

    public function testGetNamespace(): void
    {
        $this->assertEquals('meh', $this->subject->getNamespace());
    }
}
