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

namespace Crs\K8s\Service;

use Crs\K8s\Http\Exception\HttpException;
use Crs\K8s\Http\HttpClient;
use Crs\K8s\Http\UriBuilder;
use Crs\K8s\Websocket\WebsocketClientFactory;

class AbstractService
{
    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var WebsocketClientFactory
     */
    protected $websocketClientFactory;

    /**
     * @var string|null
     */
    protected $namespace;

    public function __construct(
        HttpClient $httpClient,
        UriBuilder $uriBuilder,
        WebsocketClientFactory $websocketClientFactory
    ) {
        $this->uriBuilder = $uriBuilder;
        $this->httpClient = $httpClient;
        $this->websocketClientFactory = $websocketClientFactory;
    }

    /**
     * @return $this
     */
    public function useNamespace(?string $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return mixed
     */
    protected function executeHttp(string $uri, string $action, array $options)
    {
        return $this->httpClient->send($uri, $action, $options);
    }

    /**
     * @param callable|object $handler
     * @throws HttpException
     */
    protected function executeWebsocket(string $uri, string $type, $handler): void
    {
        $wsClient = $this->websocketClientFactory->makeClient();

        $wsClient->connect($uri, $type, $handler);
    }

    /**
     * @param array|object $query
     */
    protected function makeUri(string $uri, array $parameters, $query): string
    {
        return $this->uriBuilder->buildUri(
            $uri,
            $parameters,
            $query,
            $this->namespace
        );
    }
}
