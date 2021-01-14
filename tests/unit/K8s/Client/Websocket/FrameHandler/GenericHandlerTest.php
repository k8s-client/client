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

use K8s\Client\Websocket\FrameHandler\GenericHandler;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use K8s\Core\Websocket\Frame;
use unit\K8s\Client\TestCase;

class GenericHandlerTest extends TestCase
{
    /**
     * @var \Closure|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $callable;

    /**
     * @var GenericHandler
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $callable = new class { function __invoke(string $data, WebsocketConnectionInterface $connection) {} };
        $this->callable = \Mockery::spy($callable);
        $this->subject = new GenericHandler($this->callable);
    }

    public function testOnReceiveItPassesThePayloadAndConnection(): void
    {
        $frame = \Mockery::spy(Frame::class);
        $frame->shouldReceive('getPayload')->andReturn('data');
        $connection = \Mockery::spy(WebsocketConnectionInterface::class);

        $this->subject->onReceive($frame, $connection);
        $this->callable->shouldHaveReceived('__invoke', ['data', $connection]);
    }
}
