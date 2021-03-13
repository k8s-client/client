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

class WebsocketClientFactory
{
    /**
     * @var WebsocketAdapterFactory
     */
    private $adapterFactory;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    public function __construct(
        WebsocketAdapterFactory $adapterFactory,
        RequestFactory $requestFactory
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @throws RuntimeException
     */
    public function makeClient(): WebsocketClient
    {
        return new WebsocketClient(
            $this->adapterFactory->makeAdapter(),
            $this->requestFactory
        );
    }
}
