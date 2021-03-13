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
use K8s\Client\Websocket\Contract\PortForwardInterface;
use K8s\Client\Websocket\FrameHandler\PortForwardHandler;
use K8s\Core\Exception\WebsocketException;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use K8s\Client\Websocket\FrameHandler\ExecHandler;
use Psr\Http\Message\RequestInterface;

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
            case 'portforward':
                $frameHandler = $this->makePortForwardHandler(
                    $request,
                    $handler
                );
                break;
            default:
                throw new WebsocketException(sprintf(
                    'The websocket action type "%s" is not currently supported.',
                    $type
                ));
        }

        $this->adapter->connect(
            $request,
            $frameHandler
        );
    }

    /**
     * @param callable|PortForwardInterface $handler
     */
    private function makePortForwardHandler(RequestInterface $request, $handler): PortForwardHandler
    {
        $query = $request->getUri()->getQuery();
        $ports = $this->parsePortsFromQueryString($query);

        return new PortForwardHandler(
            $handler,
            $ports
        );
    }

    private function parsePortsFromQueryString(string $query): array
    {
        $ports = [];

        $pairs = explode('&', $query);
        foreach ($pairs as $pair) {
            list($name, $value) = explode('=', $pair, 2);
            if ($name !== 'ports') {
                continue;
            }
            if (!isset($ports[$value])) {
                $ports[] = (int)$value;
            }
        }

        return $ports;
    }
}
