<?php

/**
 * This file is part of the crs/k8s library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crs\K8s\Websocket\FrameHandler;

use Crs\K8s\Exception\InvalidArgumentException;
use Crs\K8s\Websocket\Contract\ContainerExecInterface;
use Crs\K8s\Websocket\Exception\WebsocketException;
use Crs\K8s\Websocket\ExecConnection;
use Crs\K8s\Websocket\Frame;
use Crs\K8s\Websocket\Contract\FrameHandlerInterface;
use Crs\K8s\Websocket\Contract\WebsocketConnectionInterface;

class ExecHandler implements FrameHandlerInterface
{
    private const CHANNELS = [
        0 => 'stdin',
        1 => 'stdout',
        2 => 'stderr',
        3 => 'error',
        4 => 'resize',
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

    public function onReceive(Frame $frame, WebsocketConnectionInterface $connection): void
    {
        $data = $frame->getPayload();

        $channelNum = $data[0] ?? null;
        if ($channelNum === null) {
            throw new WebsocketException('Unable to determine the protocol channel from the data.');
        }
        $channel = self::CHANNELS[(int)$channelNum] ?? null;
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
}
