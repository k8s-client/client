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

use K8s\Client\Websocket\ExecConnection;
use K8s\Client\Websocket\FrameHandler\ExecHandler;
use K8s\Client\Websocket\Contract\ContainerExecInterface;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use K8s\Core\Websocket\Frame;
use unit\K8s\Client\TestCase;

class ExecHandlerTest extends TestCase
{
    /**
     * @var callable|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $callableSpy;

    /**
     * @var array|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $execReceiverSpy;

    /**
     * @var WebsocketConnectionInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $connection;

    /**
     * @var ExecHandler
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $execReceiver = new class implements ContainerExecInterface {
            public function onOpen(ExecConnection $connection): void{}
            public function onClose(): void {}
            public function onReceive(string $channel, string $data, ExecConnection $connection): void {}
        };

        $callable = new class { public function __invoke(string $channel, string $data) {}};

        $this->callableSpy = \Mockery::spy($callable);
        $this->execReceiverSpy = \Mockery::spy($execReceiver);
        $this->connection = \Mockery::spy(WebsocketConnectionInterface::class);
        $this->subject = new ExecHandler($this->callableSpy);
    }

    public function testOnConnectIsNotCalledForCallable(): void
    {
        $this->subject->onConnect($this->connection);
        $this->callableSpy->shouldNotHaveReceived('__invoke');
    }

    public function testOnCloseIsNotCalledForCallable(): void
    {
        $this->subject->onClose();
        $this->callableSpy->shouldNotHaveReceived('__invoke');
    }

    public function testOnReceiveTriggersCallable(): void
    {
        $frame = \Mockery::spy(Frame::class);
        $frame->shouldReceive([
            'getPayload' => "\x01foo",
        ]);

        $this->subject->onReceive($frame, $this->connection);
        $this->callableSpy->shouldHaveReceived(
            '__invoke',
            [
                'stdout',
                'foo',
                \Mockery::type(ExecConnection::class)
            ]
        );
    }

    public function testOnReceiveDecodesStdinChannel(): void
    {
        $frame = \Mockery::spy(Frame::class);
        $frame->shouldReceive([
            'getPayload' => "\x00foo",
        ]);

        $this->subject->onReceive($frame, $this->connection);
        $this->callableSpy->shouldHaveReceived(
            '__invoke',
            [
                'stdin',
                'foo',
                \Mockery::type(ExecConnection::class)
            ]
        );
    }

    public function testOnReceiveDecodesStderrChannel(): void
    {
        $frame = \Mockery::spy(Frame::class);
        $frame->shouldReceive([
            'getPayload' => "\x02foo",
        ]);

        $this->subject->onReceive($frame, $this->connection);
        $this->callableSpy->shouldHaveReceived(
            '__invoke',
            [
                'stderr',
                'foo',
                \Mockery::type(ExecConnection::class)
            ]
        );
    }

    public function testOnConnectIsCalledForExecClass(): void
    {
        $this->subject = new ExecHandler($this->execReceiverSpy);

        $this->subject->onConnect($this->connection);
        $this->execReceiverSpy->shouldHaveReceived('onOpen', [\Mockery::type(ExecConnection::class)]);
    }

    public function testOnCloseIsCalledForExecClass(): void
    {
        $this->subject = new ExecHandler($this->execReceiverSpy);

        $this->subject->onClose();
        $this->execReceiverSpy->shouldHaveReceived('onClose', []);
    }

    public function testOnReceiveIsCalledForExecClass(): void
    {
        $this->subject = new ExecHandler($this->execReceiverSpy);

        $frame = \Mockery::spy(Frame::class);
        $frame->shouldReceive([
            'getPayload' => "\x01foo",
        ]);

        $this->subject->onReceive($frame, $this->connection);
        $this->execReceiverSpy->shouldHaveReceived(
            'onReceive',
            [
                'stdout',
                'foo',
                \Mockery::type(ExecConnection::class)
            ]
        );
    }
}
