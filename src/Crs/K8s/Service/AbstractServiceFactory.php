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

use Crs\K8s\Http\HttpClient;
use Crs\K8s\Http\UriBuilder;
use Crs\K8s\Websocket\WebsocketClientFactory;

abstract class AbstractServiceFactory
{
    /**
     * @var UriBuilder
     */
    private $uriBuilder;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var WebsocketClientFactory
     */
    private $websocketClientFactory;

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
     * @return mixed
     */
    protected function makeService(string $serviceFqcn)
    {
        return new $serviceFqcn(
            $this->httpClient,
            $this->uriBuilder,
            $this->websocketClientFactory
        );
    }
}
