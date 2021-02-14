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

namespace unit\K8s\Client\Websocket\FrameHandler;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\Websocket\Contract\PortChannelInterface;
use K8s\Client\Websocket\Contract\PortForwardInterface;
use K8s\Client\Websocket\FrameHandler\PortForwardHandler;
use K8s\Client\Websocket\PortChannels;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use K8s\Core\Websocket\Frame;
use unit\K8s\Client\TestCase;

class PortForwardHandlerTest extends TestCase
{
    /**
     * @var PortForwardHandler
     */
    private $subject;

    /**
     * @var array|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $handler;

    /**
     * @var WebsocketConnectionInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $connection;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = \Mockery::spy(WebsocketConnectionInterface::class);
        $this->handler = \Mockery::spy(new class implements PortForwardInterface {
            public function onInitialize(PortChannels $portChannels): void {}
            public function onDataReceived(string $data, PortChannelInterface $portChannel): void {}
            public function onErrorReceived(string $data, PortChannelInterface $portChannel): void {}
            public function onClose(): void {}
        });
        $this->subject = new PortForwardHandler(
            $this->handler,
            [80, 443]
        );
    }

    public function testItHasTheCorrectSubprotocol(): void
    {
        $this->assertEquals('v4.channel.k8s.io', $this->subject->subprotocol());
    }

    public function testItInitializesThePorts(): void
    {
        $frames = [
            new Frame(0, 20, chr(0).pack('v1', 80)),
            new Frame(0, 20, chr(1).pack('v1', 80)),
            new Frame(0, 20, chr(2).pack('v1', 443)),
            new Frame(0, 20, chr(3).pack('v1', 443)),
        ];

        $this->handler->shouldReceive('onInitialize')->once();

        foreach ($frames as $frame) {
            $this->subject->onReceive($frame, $this->connection);
        }

        $this->handler->shouldHaveReceived('onInitialize');
    }

    public function testItFailsToInitializeWhenThePortDataIsIncorrect(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected 2 bytes for the port number. Received 1.');
        $this->subject->onReceive(
            new Frame(0, 20, chr(0).'1'),
            $this->connection
        );
    }

    public function testItFailsToInitializeWhenTheChannelIsAlreadyInitialized(): void
    {
        $frames = [
            new Frame(0, 20, chr(0).pack('v1', 80)),
            new Frame(0, 20, chr(0).pack('v1', 80)),
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Port channel 0 was already initialized.');

        foreach ($frames as $frame) {
            $this->subject->onReceive($frame, $this->connection);
        }
    }

    public function testItCallsOnDataAfterInitialization(): void
    {
        $frames = [
            new Frame(0, 20, chr(0).pack('v1', 80)),
            new Frame(0, 20, chr(1).pack('v1', 80)),
            new Frame(0, 20, chr(2).pack('v1', 443)),
            new Frame(0, 20, chr(3).pack('v1', 443)),
            new Frame(0, 20, chr(0).'foo'),
        ];

        foreach ($frames as $frame) {
            $this->subject->onReceive($frame, $this->connection);
        }

        $this->handler->shouldHaveReceived('onDataReceived', ['foo', \Mockery::type(PortChannelInterface::class)]);
    }

    public function testItCallsOnErrorAfterInitialization(): void
    {
        $frames = [
            new Frame(0, 20, chr(0).pack('v1', 80)),
            new Frame(0, 20, chr(1).pack('v1', 80)),
            new Frame(0, 20, chr(2).pack('v1', 443)),
            new Frame(0, 20, chr(3).pack('v1', 443)),
            new Frame(0, 20, chr(1).'error'),
        ];

        foreach ($frames as $frame) {
            $this->subject->onReceive($frame, $this->connection);
        }

        $this->handler->shouldHaveReceived('onErrorReceived', ['error', \Mockery::type(PortChannelInterface::class)]);
    }

    public function testItCallsOnClose(): void
    {
        $this->subject->onClose();

        $this->handler->shouldHaveReceived('onClose');
    }
}
