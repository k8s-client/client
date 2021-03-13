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

namespace K8s\Client;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\File\ArchiveFactory;
use K8s\Client\Http\Api;
use K8s\Client\Http\HttpClient;
use K8s\Client\Http\HttpClientFactory;
use K8s\Client\Http\RequestFactory;
use K8s\Client\Http\UriBuilder;
use K8s\Client\Kind\KindManager;
use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Metadata\MetadataParser;
use K8s\Client\Serialization\Contract\DenormalizerInterface;
use K8s\Client\Serialization\Contract\NormalizerInterface;
use K8s\Client\Serialization\ModelDenormalizer;
use K8s\Client\Serialization\ModelNormalizer;
use K8s\Client\Serialization\Serializer;
use K8s\Api\Service\ServiceFactory;
use K8s\Client\Websocket\WebsocketAdapterFactory;
use K8s\Client\Websocket\WebsocketClientFactory;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use K8s\Core\Contract\ApiInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Factory
{
    /**
     * @var ContextConfigFactory|null
     */
    private $contextConfigFactory = null;

    /**
     * @var ServiceFactory|null
     */
    private $serviceFactory = null;

    /**
     * @var HttpClientFactory|null
     */
    private $httpClientFactory = null;

    /**
     * @var HttpClient|null
     */
    private $httpClient = null;

    /**
     * @var RequestFactory|null
     */
    private $requestFactory = null;

    /**
     * @var Serializer|null
     */
    private $serializer = null;

    /**
     * @var UriBuilder|null
     */
    private $uriBuilder = null;

    /**
     * @var WebsocketAdapterFactory|null
     */
    private $websocketAdapterFactory = null;

    /**
     * @var WebsocketClientFactory|null
     */
    private $websocketClientFactory = null;

    /**
     * @var MetadataCache|null
     */
    private $metadataCache = null;

    /**
     * @var KindManager|null
     */
    private $kindManager = null;

    /**
     * @var ApiInterface|null
     */
    private $api = null;

    /**
     * @var NormalizerInterface|null
     */
    private $normalizer = null;

    /**
     * @var DenormalizerInterface|null
     */
    private $denormalizer = null;

    /**
     * @var ArchiveFactory|null
     */
    private $archiveFactory = null;

    /**
     * @var Options
     */
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function makeServiceFactory(): ServiceFactory
    {
        if ($this->serviceFactory) {
            return $this->serviceFactory;
        }
        $this->serviceFactory = new ServiceFactory($this->makeApi());

        return $this->serviceFactory;
    }

    public function makeNormalizer(): NormalizerInterface
    {
        if ($this->normalizer) {
            return $this->normalizer;
        }
        $this->normalizer = new ModelNormalizer($this->makeMetadataCache());

        return $this->normalizer;
    }

    public function makeDenormalizer(): DenormalizerInterface
    {
        if ($this->denormalizer) {
            return $this->denormalizer;
        }
        $this->denormalizer = new ModelDenormalizer($this->makeMetadataCache());

        return $this->denormalizer;
    }

    public function makeSerializer(): Serializer
    {
        if ($this->serializer) {
            return $this->serializer;
        }

        $this->serializer = new Serializer(
            $this->makeNormalizer(),
            $this->makeDenormalizer()
        );

        return $this->serializer;
    }

    public function makeMetadataCache(): MetadataCache
    {
        if ($this->metadataCache) {
            return $this->metadataCache;
        }
        $this->metadataCache = new MetadataCache(
            new MetadataParser(),
            $this->options->getCache()
        );

        return $this->metadataCache;
    }

    public function makeRequestFactory(): RequestFactory
    {
        if ($this->requestFactory) {
            return $this->requestFactory;
        }

        try {
            $this->requestFactory = new RequestFactory(
                $this->options->getHttpRequestFactory() ?? Psr17FactoryDiscovery::findRequestFactory(),
                $this->makeStreamFactory(),
                $this->options->getHttpUriFactory() ?? Psr17FactoryDiscovery::findUriFactory(),
                $this->makeContextConfigFactory()
            );
        } catch (NotFoundException $exception) {
            throw new RuntimeException(
                'You must provide a PSR-18 compatible HTTP Client and a PSR-17 compatible request / stream factory.',
                $exception->getCode(),
                $exception
            );
        }

        return $this->requestFactory;
    }

    public function makeStreamFactory(): StreamFactoryInterface
    {
        try {
            return $this->options->getStreamFactory() ?? Psr17FactoryDiscovery::findStreamFactory();
        } catch (NotFoundException $exception) {
            throw new RuntimeException(
                'You must install or provide a PSR-17 compatible stream factory.',
                $exception->getCode(),
                $exception
            );
        }
    }

    public function makeArchiveFactory(): ArchiveFactory
    {
        if ($this->archiveFactory) {
            return $this->archiveFactory;
        }
        $this->archiveFactory = new ArchiveFactory($this->makeStreamFactory());

        return $this->archiveFactory;
    }

    public function makeHttpClientFactory(): HttpClientFactory
    {
        if ($this->httpClientFactory) {
            return $this->httpClientFactory;
        }
        $this->httpClientFactory = new HttpClientFactory(
            $this->makeContextConfigFactory(),
            $this->options->getHttpClient(),
            $this->options->getHttpClientFactory()
        );

        return $this->httpClientFactory;
    }

    public function makeHttpClient(): HttpClient
    {
        if ($this->httpClient) {
            return $this->httpClient;
        }
        $this->httpClient = new HttpClient(
            $this->makeRequestFactory(),
            $this->makeHttpClientFactory(),
            $this->makeSerializer()
        );

        return $this->httpClient;
    }

    public function makeUriBuilder(): UriBuilder
    {
        if ($this->uriBuilder) {
            return $this->uriBuilder;
        }
        $this->uriBuilder = new UriBuilder($this->options);

        return $this->uriBuilder;
    }

    public function makeContextConfigFactory(): ContextConfigFactory
    {
        if ($this->contextConfigFactory) {
            return $this->contextConfigFactory;
        }
        $this->contextConfigFactory = new ContextConfigFactory($this->options);

        return $this->contextConfigFactory;
    }

    public function makeWebsocketAdapterFactory(): WebsocketAdapterFactory
    {
        if ($this->websocketAdapterFactory) {
            return $this->websocketAdapterFactory;
        }
        $this->websocketAdapterFactory = new WebsocketAdapterFactory(
            $this->options,
            $this->makeContextConfigFactory()
        );

        return $this->websocketAdapterFactory;
    }

    public function makeWebsocketClientFactory(): WebsocketClientFactory
    {
        if ($this->websocketClientFactory) {
            return $this->websocketClientFactory;
        }
        $this->websocketClientFactory = new WebsocketClientFactory(
            $this->makeWebsocketAdapterFactory(),
            $this->makeRequestFactory()
        );

        return $this->websocketClientFactory;
    }

    public function makeKindManager(): KindManager
    {
        if ($this->kindManager) {
            return $this->kindManager;
        }
        $this->kindManager = new KindManager(
            $this->makeHttpClient(),
            $this->makeUriBuilder(),
            $this->makeMetadataCache(),
            $this->options
        );

        return $this->kindManager;
    }

    public function makeApi(): ApiInterface
    {
        if ($this->api) {
            return $this->api;
        }
        $this->api = new Api(
            $this->makeHttpClient(),
            $this->makeWebsocketClientFactory(),
            $this->makeUriBuilder()
        );

        return $this->api;
    }
}
