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

namespace K8s\Client\Websocket\Contract;

use K8s\Client\Websocket\PortChannels;

interface PortForwardInterface
{
    /**
     * Called after all port channels have been initialized.
     */
    public function onInitialize(PortChannels $portChannels): void;

    /**
     * Called when data has been received on a port channel.
     */
    public function onDataReceived(string $data, PortChannelInterface $portChannel): void;

    /**
     * Called when an error has been received on a port channel.
     */
    public function onErrorReceived(string $data, PortChannelInterface $portChannel): void;

    /**
     * Called when the underlying websocket for the port channels has been closed.
     */
    public function onClose(): void;
}
