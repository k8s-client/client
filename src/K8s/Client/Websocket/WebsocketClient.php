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

use K8s\Client\Http\RequestFactory;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use K8s\Client\Websocket\FrameHandler\ExecHandler;
use K8s\Client\Websocket\FrameHandler\GenericHandler;

class WebsocketClient
{
    private const SUB_PROTOCOL = 'channel.k8s.io';

    /**
     * @var WebsocketClientInterface
     */
    private $adapter;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    public function __construct(
        WebsocketClientInterface $adapter,
        RequestFactory $requestFactory
    ) {
        $this->adapter = $adapter;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param mixed $handler
     */
    public function connect(string $uri, string $type, $handler): void
    {
        $request = $this->requestFactory->makeRequest($uri, 'connect');
        $request = $request->withUri(
            $request->getUri()->withScheme('wss')
        );

        switch ($type) {
            case 'exec':
                $frameHandler = new ExecHandler($handler);
                break;
            default:
                $frameHandler = new GenericHandler($handler);
        }

        $this->adapter->connect(
            $request,
            $frameHandler
        );
    }
}
