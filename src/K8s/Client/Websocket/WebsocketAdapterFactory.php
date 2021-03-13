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
use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Client\Options;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;

class WebsocketAdapterFactory
{
    /**
     * @var class-string[]
     */
    private const ADAPTERS = [
        'K8s\WsRatchet\RatchetWebsocketAdapter',
        'K8s\WsSwoole\CoroutineAdapter',
    ];

    /**
     * @var ContextConfigFactory
     */
    private $configFactory;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var class-string[]
     */
    private $adapterClasses;

    public function __construct(
        Options $options,
        ContextConfigFactory $configFactory,
        array $adapterClasses = self::ADAPTERS
    ) {
        $this->options = $options;
        $this->configFactory = $configFactory;
        $this->adapterClasses = $adapterClasses;
    }

    public function makeAdapter(): WebsocketClientInterface
    {
        if ($this->options->getWebsocketClient()) {
            return $this->options->getWebsocketClient();
        }

        if ($this->options->getWebsocketClientFactory()) {
            return $this->options->getWebsocketClientFactory()
                ->makeClient($this->configFactory->makeContextConfig());
        }

        foreach ($this->adapterClasses as $adapter) {
            if (class_exists($adapter)) {
                return new $adapter();
            }
        }

        throw new RuntimeException(
            'To use Kubernetes API requests that require websockets, you must install a websocket library. See this libraries documentation for more information.'
        );
    }
}
