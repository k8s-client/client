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

namespace integration\K8s\Client\Kind;

use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\Websocket\Contract\PortChannelInterface;
use K8s\Client\Websocket\Contract\PortForwardInterface;
use K8s\Client\Websocket\PortChannels;

class PortForwardTest extends TestCase
{
    /**
     * @var PortForwardInterface
     */
    private $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = new class implements PortForwardInterface
        {
            /**
             * @var string
             */
            public $received = '';

            /**
             * @var PortChannels
             */
            private $portChannels;

            /**
             * @inheritDoc
             */
            public function onInitialize(PortChannels $portChannels) : void
            {
                $this->portChannels = $portChannels;

                $data = "GET / HTTP/1.1\r\n";
                $data .= "Host: 127.0.0.1\r\n";
                $data .= "Connection: close\r\n";
                $data .= "Accept: */*\r\n";
                $data .= "\r\n";

                $this->portChannels->writeToPort(80, $data);
            }

            /**
             * @inheritDoc
             */
            public function onDataReceived(string $data, PortChannelInterface $portChannel) : void
            {
                $this->received .= $data;
            }

            /**
             * @inheritDoc
             */
            public function onErrorReceived(string $data, PortChannelInterface $portChannel) : void
            {
                throw new \RuntimeException(sprintf(
                    'Received error on port %s: %s',
                    $portChannel->getPortNumber(),
                    $data
                ));
            }

            /**
             * @inheritDoc
             */
            public function onClose() : void
            {
            }
        };
    }

    public function testItPortForwardsAndReceivesData(): void
    {
        $this->createAndWaitForPod(new Pod(
            'portforward-example',
            [new Container('portforward-example', 'nginx:latest')]
        ));

        $this->k8s()
            ->portforward('portforward-example', 80)
            ->start($this->handler);

        $this->assertStringContainsString('Welcome to nginx', $this->handler->received);
    }
}
