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

namespace K8s\Client\Http;

use K8s\Client\Websocket\WebsocketClientFactory;
use K8s\Core\Contract\ApiInterface;

class Api implements ApiInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var WebsocketClientFactory
     */
    private $websocketClientFactory;

    /**
     * @var UriBuilder
     */
    private $uriBuilder;

    public function __construct(
        HttpClient $httpClient,
        WebsocketClientFactory $websocketClientFactory,
        UriBuilder $uriBuilder
    ) {
        $this->httpClient = $httpClient;
        $this->websocketClientFactory = $websocketClientFactory;
        $this->uriBuilder = $uriBuilder;
    }

    /**
     * @inheritDoc
     */
    public function executeHttp(string $uri, string $action, array $options)
    {
        return $this->httpClient->send($uri, $action, $options);
    }

    /**
     * @inheritDoc
     */
    public function executeWebsocket(string $uri, string $type, $handler): void
    {
        $wsClient = $this->websocketClientFactory->makeClient();

        $wsClient->connect($uri, $type, $handler);
    }

    /**
     * @inheritDoc
     */
    public function makeUri(string $uri, array $parameters, array $query = [], ?string $namespace = null): string
    {
        return $this->uriBuilder->buildUri(
            $uri,
            $parameters,
            $query,
            $namespace
        );
    }
}
