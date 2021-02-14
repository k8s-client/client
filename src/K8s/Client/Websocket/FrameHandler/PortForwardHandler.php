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

namespace K8s\Client\Websocket\FrameHandler;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\Websocket\Contract\PortChannelInterface;
use K8s\Client\Websocket\Contract\PortForwardInterface;
use K8s\Client\Websocket\PortChannel;
use K8s\Client\Websocket\PortChannels;
use K8s\Core\Websocket\Contract\FrameHandlerInterface;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use K8s\Core\Websocket\Frame;

class PortForwardHandler implements FrameHandlerInterface
{
    /**
     * @var callable|PortForwardInterface
     */
    private $handler;

    /**
     * @var integer[]
     */
    private $ports;

    /**
     * @var array<int, PortChannelInterface>
     */
    private $initialized = [];

    /**
     * @var PortChannels|null
     */
    private $channels = null;

    /**
     * @param callable|PortForwardInterface $handler
     * @param int[] $ports
     */
    public function __construct($handler, array $ports)
    {
        $this->ports = $ports;
        $this->handler = $handler;
    }

    /**
     * @inheritDoc
     */
    public function onConnect(WebsocketConnectionInterface $connection): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onClose(): void
    {
        if ($this->handler instanceof PortForwardInterface) {
            $this->handler->onClose();
        }
    }

    /**
     * @inheritDoc
     */
    public function onReceive(Frame $frame, WebsocketConnectionInterface $connection): void
    {
        $data = $frame->getPayload();
        $channel = ord($data[0]);
        $data = substr($data, 1);

        if ($this->channels === null) {
            $this->initialize($channel, $data, $connection);

            return;
        }

        $portChannel = $this->channels->getByChannel($channel);
        if ($portChannel->getType() === PortChannelInterface::TYPE_DATA) {
            $this->dispatchData($portChannel, $data);
        } else {
            $this->dispatchError($portChannel, $data);
        }
    }

    /**
     * @inheritDoc
     */
    public function subprotocol(): string
    {
        return 'v4.channel.k8s.io';
    }

    private function initialize(int $channel, string $data, WebsocketConnectionInterface $connection): void
    {
        if (strlen($data) !== 2) {
            throw new RuntimeException(sprintf(
                'Expected 2 bytes for the port number. Received %s.',
                strlen($data)
            ));
        }
        if (isset($this->initialized[$channel])) {
            throw new RuntimeException(sprintf(
                'Port channel %s was already initialized.',
                $channel
            ));
        }
        $result = unpack('v1port', $data);
        if ($result === false || !isset($result['port'])) {
            throw new RuntimeException(
                'Unable to determine the channel port number from the received data.'
            );
        }
        $this->initialized[$channel] = new PortChannel(
            $connection,
            $channel,
            $result['port']
        );

        # We will always have 2 channels per port (data and error) once initialized
        if (count($this->initialized) !== (count($this->ports) * 2)) {
            return;
        }

        $this->channels = new PortChannels(...$this->initialized);
        if ($this->handler instanceof PortForwardInterface) {
            $this->handler->onInitialize($this->channels);
        }
    }

    public function dispatchData(PortChannelInterface $portChannel, string $data): void
    {
        if ($this->handler instanceof PortForwardInterface) {
            $this->handler->onDataReceived($data, $portChannel);
        } else {
            call_user_func($this->handler, $data, $portChannel);
        }
    }

    public function dispatchError(PortChannelInterface $portChannel, string $data): void
    {
        if ($this->handler instanceof PortForwardInterface) {
            $this->handler->onErrorReceived($data, $portChannel);
        } else {
            call_user_func($this->handler, $data, $portChannel);
        }
    }
}
