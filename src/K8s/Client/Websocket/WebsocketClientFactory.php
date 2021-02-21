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

use K8s\Client\Exception\RuntimeException;
use K8s\Client\Http\RequestFactory;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;

class WebsocketClientFactory
{
    private const ADAPTERS = [
        'K8s\WsRatchet\RatchetWebsocketAdapter',
    ];

    /**
     * @var class-string[]
     */
    private $adapters;

    /**
     * @var WebsocketClientInterface|null
     */
    private $wsAdapter;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @param array|string[] $adapters
     */
    public function __construct(
        ?WebsocketClientInterface $wsAdapter,
        RequestFactory $requestFactory,
        array $adapters = self::ADAPTERS
    ) {
        $this->wsAdapter = $wsAdapter;
        $this->requestFactory = $requestFactory;
        $this->adapters = $adapters;
    }

    /**
     * @throws RuntimeException
     */
    public function makeClient(): WebsocketClient
    {
        if ($this->wsAdapter) {
            return new WebsocketClient(
                $this->wsAdapter,
                $this->requestFactory
            );
        }

        foreach ($this->adapters as $client) {
            if (class_exists($client)) {
                return new WebsocketClient(
                    new $client(),
                    $this->requestFactory
                );
            }
        }

        throw new RuntimeException(
            'To use Kubernetes API requests that require websockets, you must install a websocket library. See this libraries documentation for more information.'
        );
    }
}
