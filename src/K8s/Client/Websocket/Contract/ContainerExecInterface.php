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

use K8s\Client\Websocket\ExecConnection;

interface ContainerExecInterface
{
    public const CHANNEL_STDOUT = 'stdout';

    public const CHANNEL_STDIN = 'stdin';

    public const CHANNEL_STDERR = 'stderr';

    public const CHANNEL_RESIZE = 'resize';

    public const CHANNEL_ERROR = 'error';

    /**
     * Triggered when the connection is initially opened.
     */
    public function onOpen(ExecConnection $connection): void;

    /**
     * Triggered once the connection is closed.
     */
    public function onClose(): void;

    /**
     * Triggered when data is received on a specific channel.
     */
    public function onReceive(string $channel, string $data, ExecConnection $connection): void;
}
