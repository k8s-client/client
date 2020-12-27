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

namespace Crs\K8s\Websocket\Contract;

use Crs\K8s\Websocket\Frame;

interface FrameHandlerInterface
{
    /**
     * Triggered on the initial connection
     */
    public function onConnect(WebsocketConnectionInterface $connection): void;

    /**
     * Triggered when the connection is closed.
     */
    public function onClose(): void;

    /**
     * Triggered when data is received on the connection.
     */
    public function onReceive(Frame $frame, WebsocketConnectionInterface $connection): void;
}
