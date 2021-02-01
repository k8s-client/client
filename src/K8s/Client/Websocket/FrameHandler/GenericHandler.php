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
use K8s\Core\Websocket\Contract\FrameHandlerInterface;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use K8s\Core\Websocket\Frame;

class GenericHandler implements FrameHandlerInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @param callable $callable
     */
    public function __construct($callable)
    {
        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf(
                'Expected a callable for a generic websocket handler. Got: %s',
                gettype($callable)
            ));
        }
        $this->callable = $callable;
    }

    public function onReceive(Frame $frame, WebsocketConnectionInterface $connection): void
    {
        call_user_func(
            $this->callable,
            $frame->getPayload(),
            $connection
        );
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
    }

    /**
     * @inheritDoc
     */
    public function subprotocol(): string
    {
        return 'channel.k8s.io';
    }
}
