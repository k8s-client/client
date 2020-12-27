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

use Crs\K8s\Exception\RuntimeException;
use Crs\K8s\Http\RequestFactory;
use Crs\K8s\Websocket\Contract\WebsocketClientInterface;

class WebsocketClientFactory
{
    private const ADAPTERS = [
        'Crs\K8sWsRatchet\RatchetWebsocketAdapter',
    ];

    /**
     * @var WebsocketClientInterface|null
     */
    private $wsAdapter;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    public function __construct(
        ?WebsocketClientInterface $wsAdapter,
        RequestFactory $requestFactory
    ) {
        $this->wsAdapter = $wsAdapter;
        $this->requestFactory = $requestFactory;
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

        foreach (self::ADAPTERS as $client) {
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
