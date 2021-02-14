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

use ArrayIterator;
use IteratorAggregate;
use K8s\Client\Exception\RuntimeException;
use K8s\Client\Websocket\Contract\PortChannelInterface;
use Traversable;

class PortChannels implements IteratorAggregate
{
    /**
     * @var array<int, PortChannelInterface>
     */
    private $portChannels;

    public function __construct(PortChannelInterface ...$portChannels)
    {
        $this->portChannels = $portChannels;
    }

    /**
     * Get a port channel by its port number.
     */
    public function getByPort(int $port, string $type = PortChannelInterface::TYPE_DATA): PortChannelInterface
    {
        foreach ($this->portChannels as $portChannel) {
            if ($portChannel->getPortNumber() === $port && $portChannel->getType() === $type) {
                return $portChannel;
            }
        }

        throw new RuntimeException(sprintf(
            'Port %s of type %s is not an initialized port.',
            $port,
            $type
        ));
    }

    /**
     * Get a part channel by the channel number.
     */
    public function getByChannel(int $channel): PortChannelInterface
    {
        foreach ($this->portChannels as $portChannel) {
            if ($portChannel->getChannelNumber() === $channel) {
                return $portChannel;
            }
        }

        throw new RuntimeException(sprintf(
            'Channel %s is not an initialized channel.',
            $channel
        ));
    }

    /**
     * Write data to the port channel with the given port number.
     *
     * @param int $port
     * @param string $data
     */
    public function writeToPort(int $port, string $data): void
    {
        $portChannel = $this->getByPort($port);
        $portChannel->write($data);
    }

    /**
     * @return array<int, PortChannelInterface>
     */
    public function toArray(): array
    {
        return $this->portChannels;
    }

    /**
     * @return ArrayIterator<int, PortChannelInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->portChannels);
    }
}
