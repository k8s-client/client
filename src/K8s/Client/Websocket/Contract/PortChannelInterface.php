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

interface PortChannelInterface
{
    public const TYPE_DATA = 'data';

    public const TYPE_ERROR = 'error';

    /**
     * The type of channel. Either "data" or "error".
     */
    public function getType(): string;

    /**
     * The port number for this channel.
     */
    public function getPortNumber(): int;

    /**
     * The underlying channel number the port channel.
     */
    public function getChannelNumber(): int;

    /**
     * Close the websocket connection for the port-forward.
     */
    public function close(): void;

    /**
     * Write data to the channel. Only possible for channel type "data".
     */
    public function write(string $data): void;
}
