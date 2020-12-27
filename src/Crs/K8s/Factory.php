<?php

declare(strict_types=1);

namespace Crs\K8s;

use Crs\K8s\Exception\RuntimeException;
use Crs\K8s\Http\HttpClient;
use Crs\K8s\Http\RequestFactory;
use Crs\K8s\Http\UriBuilder;
use Crs\K8s\Kind\KindManager;
use Crs\K8s\Metadata\MetadataCache;
use Crs\K8s\Metadata\MetadataParser;
use Crs\K8s\Serialization\Serializer;
use Crs\K8s\Service\ServiceFactory;
use Crs\K8s\Websocket\WebsocketClientFactory;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

class Factory
{
    /**
     * @var ServiceFactory|null
     */
    private $serviceFactory;

    /**
     * @var HttpClient|null
     */
    private $httpClient;

    /**
     * @var RequestFactory|null
     */
    private $requestFactory;

    /**
     * @var Serializer|null
     */
    private $serializer;

    /**
     * @var UriBuilder|null
     */
    private $uriBuilder;

    /**
     * @var WebsocketClientFactory|null
     */
    private $websocketClientFactory;

    /**
     * @var MetadataCache|null
     */
    private $metadataCache;

    /**
     * @var KindManager|null
     */
    private $kindManager;

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
        $this->serviceFactory = new ServiceFactory(
            $this->makeHttpClient(),
            $this->makeUriBuilder(),
            $this->makeWebsocketClientFactory()
        );

        return $this->serviceFactory;
    }

    public function makeSerializer(): Serializer
    {
        if ($this->serializer) {
            return $this->serializer;
        }

        $this->serializer = new Serializer(
            new MetadataCache(
                new MetadataParser(),
                $this->options->getCache()
            )
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
                $this->options->getStreamFactory() ?? Psr17FactoryDiscovery::findStreamFactory(),
                $this->options
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

    public function makeHttpClient(): HttpClient
    {
        if ($this->httpClient) {
            return $this->httpClient;
        }

        try {
            $this->httpClient = new HttpClient(
                $this->makeRequestFactory(),
                $this->options->getHttpClient() ?? Psr18ClientDiscovery::find(),
                $this->makeSerializer()
            );
        } catch (NotFoundException $exception) {
            throw new RuntimeException(
                'You must provide a PSR-18 compatible HTTP Client and a PSR-17 compatible request / stream factory.',
                $exception->getCode(),
                $exception
            );
        }

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

    public function makeWebsocketClientFactory(): WebsocketClientFactory
    {
        if ($this->websocketClientFactory) {
            return $this->websocketClientFactory;
        }
        $this->websocketClientFactory = new WebsocketClientFactory(
            $this->options->getWebsocketClient(),
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
}
