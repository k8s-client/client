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

use K8s\Client\Exception\RuntimeException;
use K8s\Client\Websocket\Contract\PortChannelInterface;
use K8s\Client\Websocket\PortChannel;
use K8s\Client\Websocket\PortChannels;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use unit\K8s\Client\TestCase;

class PortChannelsTest extends TestCase
{
    /**
     * @var WebsocketConnectionInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $connection;

    /**
     * @var PortChannel|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $channel1;

    /**
     * @var PortChannel|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $channel2;

    /**
     * @var PortChannels
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = \Mockery::spy(WebsocketConnectionInterface::class);
        $this->channel1 = \Mockery::spy(new PortChannel($this->connection, 0, 80));
        $this->channel2 = \Mockery::spy(new PortChannel($this->connection, 1, 80));

        $this->subject = new PortChannels(
            $this->channel1,
            $this->channel2
        );
    }

    public function testGetByPortWithData(): void
    {
        $result = $this->subject->getByPort(80);

        $this->assertEquals($this->channel1, $result);
    }

    public function testGetByPortWithError(): void
    {
        $result = $this->subject->getByPort(80, PortChannelInterface::TYPE_ERROR);

        $this->assertEquals($this->channel2, $result);
    }

    public function testGetByChannel(): void
    {
        $result = $this->subject->getByChannel(0);

        $this->assertEquals($this->channel1, $result);
    }

    public function testWriteToPort(): void
    {
        $this->subject->writeToPort(80, 'foo');

        $this->channel1->shouldHaveReceived('write', ['foo']);
    }

    public function testItThrowsExceptionWhenNoChannelExists(): void
    {
        $this->expectException(RuntimeException::class);

        $this->subject->getByChannel(9999);
    }

    public function testItThrowsExceptionWhenNoPortExists(): void
    {
        $this->expectException(RuntimeException::class);

        $this->subject->getByPort(9999);
    }

    public function testToArray(): void
    {
        $this->assertEquals([$this->channel1, $this->channel2], $this->subject->toArray());
    }
}
