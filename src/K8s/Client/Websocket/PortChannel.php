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

namespace K8s\Client\Websocket;

use K8s\Client\Websocket\Contract\PortChannelInterface;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;

class PortChannel implements PortChannelInterface
{
    /**
     * @var WebsocketConnectionInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $channel;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $type;

    public function __construct(WebsocketConnectionInterface $connection, int $channel, int $port)
    {
        $this->connection = $connection;
        $this->channel = $channel;
        $this->port = $port;
        $this->type = ($channel % 2) ? self::TYPE_ERROR : self::TYPE_DATA;
    }

    public function getPortNumber(): int
    {
        return $this->port;
    }

    public function close(): void
    {
        $this->connection->close();
    }

    public function write(string $data): void
    {
        $this->connection->send(chr($this->channel).$data);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getChannelNumber(): int
    {
        return $this->channel;
    }
}
