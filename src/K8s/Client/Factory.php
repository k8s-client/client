<?php

declare(strict_types=1);

namespace K8s\Client;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\File\ArchiveFactory;
use K8s\Client\Http\Api;
use K8s\Client\Http\HttpClient;
use K8s\Client\Http\RequestFactory;
use K8s\Client\Http\UriBuilder;
use K8s\Client\Kind\KindManager;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Metadata\MetadataParser;
use K8s\Client\Serialization\Contract\DenormalizerInterface;
use K8s\Client\Serialization\Contract\NormalizerInterface;
use K8s\Client\Serialization\ModelDenormalizer;
use K8s\Client\Serialization\ModelNormalizer;
use K8s\Client\Serialization\Serializer;
use K8s\Api\Service\ServiceFactory;
use K8s\Client\Websocket\WebsocketClientFactory;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use K8s\Core\Contract\ApiInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
     * @var ApiInterface|null
     */
    private $api;

    /**
     * @var NormalizerInterface|null
     */
    private $normalizer;

    /**
     * @var DenormalizerInterface|null
     */
    private $denormalizer;

    /**
     * @var ArchiveFactory|null
     */
    private $archiveFactory;

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
