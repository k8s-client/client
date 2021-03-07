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

use K8s\Client\KubeConfig\Model\UserExec;
use unit\K8s\Client\TestCase;

class UserExecTest extends TestCase
{
    /**
     * @var UserExec
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new UserExec([
            'command' => 'get-token',
            'args' => ['foo', 'bar'],
            'env' => [
                'USERNAME' => 'foo',
                'PASSWORD' => 'bar',
            ],
            'apiVersion' => 'v1',
            'provideClusterInfo' => false,
            'installHint' => 'do stuff',
        ]);
    }

    public function testGetCommand(): void
    {
        $this->assertEquals('get-token', $this->subject->getCommand());
    }

    public function testGetApiVersion(): void
    {
        $this->assertEquals('v1', $this->subject->getApiVersion());
    }

    public function testGetEnv(): void
    {
        $this->assertEquals(
            ['USERNAME' => 'foo', 'PASSWORD' => 'bar'],
            $this->subject->getEnv()
        );
    }

    public function testGetArgs(): void
    {
        $this->assertEquals(
            ['foo', 'bar'],
            $this->subject->getArgs()
        );
    }

    public function testGetInstallHint(): void
    {
        $this->assertEquals('do stuff', $this->subject->getInstallHint());
    }

    public function testIsProviderClusterInfo(): void
    {
        $this->assertFalse($this->subject->isProviderClusterInfo());
    }
}
