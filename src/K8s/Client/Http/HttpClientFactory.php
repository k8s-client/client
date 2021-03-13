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

namespace K8s\Client\Http;

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr18ClientDiscovery;
use K8s\Client\Exception\RuntimeException;
use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Core\Contract\HttpClientFactoryInterface;
use Psr\Http\Client\ClientInterface;

class HttpClientFactory
{
    /**
     * @var ClientInterface|null
     */
    private $client;

    /**
     * @var HttpClientFactoryInterface|null
     */
    private $httpClientFactory;

    /**
     * @var ContextConfigFactory
     */
    private $configFactory;

    public function __construct(
        ContextConfigFactory $configFactory,
        ?ClientInterface $client = null,
        ?HttpClientFactoryInterface $httpClientFactory = null
    ) {
        $this->configFactory = $configFactory;
        $this->client = $client;
        $this->httpClientFactory = $httpClientFactory;
    }

    public function makeClient(bool $isStreaming): ClientInterface
    {
        if ($this->client) {
            return $this->client;
        }

        if ($this->httpClientFactory) {
            return $this->httpClientFactory->makeClient(
                $this->configFactory->makeContextConfig(),
                $isStreaming
            );
        }

        try {
            return Psr18ClientDiscovery::find();
        } catch (NotFoundException $exception) {
            throw new RuntimeException(
                'You must provide a PSR-18 compatible HTTP Client and a PSR-17 compatible request / stream factory.',
                $exception->getCode(),
                $exception
            );
        }
    }
}
