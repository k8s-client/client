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

namespace unit\K8s\Client\Websocket;

use K8s\Client\Websocket\Contract\PortChannelInterface;
use K8s\Client\Websocket\PortChannel;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use unit\K8s\Client\TestCase;

class PortChannelTest extends TestCase
{
    /**
     * @var WebsocketConnectionInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $connection;

    /**
     * @var PortChannel
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = \Mockery::spy(WebsocketConnectionInterface::class);
        $this->subject = new PortChannel(
            $this->connection,
            0,
            80
        );
    }

    public function testGetPortNumber(): void
    {
        $this->assertEquals(80, $this->subject->getPortNumber());
    }

    public function testGetChannelNumber(): void
    {
        $this->assertEquals(0, $this->subject->getChannelNumber());
    }

    public function testClose(): void
    {
        $this->subject->close();
        $this->connection->shouldHaveReceived('close');
    }

    public function testWrite(): void
    {
        $this->subject->write('foo');
        $this->connection->shouldHaveReceived('send', [chr(0).'foo']);
    }

    public function testGetTypeWithData(): void
    {
        $this->assertEquals(PortChannelInterface::TYPE_DATA, $this->subject->getType());
    }

    public function testGetTypeWithError(): void
    {
        $this->subject = new PortChannel(
            $this->connection,
            1,
            80
        );

        $this->assertEquals(PortChannelInterface::TYPE_ERROR, $this->subject->getType());
    }
}
