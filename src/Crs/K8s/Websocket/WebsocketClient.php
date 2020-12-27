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

namespace Crs\K8s\Websocket;

use Crs\K8s\Http\RequestFactory;
use Crs\K8s\Websocket\Contract\WebsocketClientInterface;
use Crs\K8s\Websocket\FrameHandler\ExecHandler;
use Crs\K8s\Websocket\FrameHandler\GenericHandler;

class WebsocketClient
{
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

        $this->adapter->connect('channel.k8s.io', $request, $frameHandler);
    }
}
