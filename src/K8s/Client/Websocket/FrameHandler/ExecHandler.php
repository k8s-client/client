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

use K8s\Client\Exception\InvalidArgumentException;
use K8s\Client\Websocket\ExecConnection;
use K8s\Client\Websocket\Contract\ContainerExecInterface;
use K8s\Core\Exception\WebsocketException;
use K8s\Core\Websocket\Frame;
use K8s\Core\Websocket\Contract\FrameHandlerInterface;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;

class ExecHandler implements FrameHandlerInterface
{
    private const CHANNELS = [
        0 => ExecConnection::CHANNEL_STDIN,
        1 => ExecConnection::CHANNEL_STDOUT,
        2 => ExecConnection::CHANNEL_STDERR,
        3 => ExecConnection::CHANNEL_ERROR,
        4 => ExecConnection::CHANNEL_RESIZE,
    ];

    /**
     * @var callable|ContainerExecInterface
     */
    private $receiver;

    /**
     * @param callable|ContainerExecInterface $receiver
     */
    public function __construct($receiver)
    {
        if (!(is_callable($receiver) || $receiver instanceof ContainerExecInterface)) {
            throw new InvalidArgumentException(
                'When executing against a container you must supply a callable or ContainerExecInterface instance.'
            );
        }
        $this->receiver = $receiver;
    }

    /**
     * @inheritDoc
     */
    public function onReceive(Frame $frame, WebsocketConnectionInterface $connection): void
    {
        $data = $frame->getPayload();

        $channelNum = $data[0] ?? null;
        if ($channelNum === null) {
            throw new WebsocketException('Unable to determine the protocol channel from the data.');
        }
        $channel = self::CHANNELS[ord($channelNum)] ?? null;
        if ($channel === null) {
            throw new WebsocketException(sprintf(
                'The channel number %s is not recognized.',
                $channelNum
            ));
        }
        $execConn = new ExecConnection($connection);
        $data = (string)substr($data, 1);

        if (is_callable($this->receiver)) {
            call_user_func(
                $this->receiver,
                $channel,
                $data,
                $execConn
            );
        } else {
            $this->receiver->onReceive(
                $channel,
                $data,
                $execConn
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function onConnect(WebsocketConnectionInterface $connection): void
    {
        if ($this->receiver instanceof ContainerExecInterface) {
            $this->receiver->onOpen(new ExecConnection($connection));
        }
    }

    /**
     * @inheritDoc
     */
    public function onClose(): void
    {
        if ($this->receiver instanceof ContainerExecInterface) {
            $this->receiver->onClose();
        }
    }

    /**
     * @inheritDoc
     */
    public function subprotocol(): string
    {
        return 'channel.k8s.io';
    }
}
