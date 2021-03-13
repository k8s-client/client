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

namespace unit\K8s\Client;

use K8s\Api\Service\ServiceFactory;
use K8s\Client\Factory;
use K8s\Client\File\ArchiveFactory;
use K8s\Client\Http\HttpClient;
use K8s\Client\Http\HttpClientFactory;
use K8s\Client\Http\RequestFactory;
use K8s\Client\Http\UriBuilder;
use K8s\Client\Kind\KindManager;
use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Options;
use K8s\Client\Serialization\Contract\DenormalizerInterface;
use K8s\Client\Serialization\Contract\NormalizerInterface;
use K8s\Client\Serialization\Serializer;
use K8s\Client\Websocket\WebsocketAdapterFactory;
use K8s\Client\Websocket\WebsocketClientFactory;
use K8s\Core\Contract\ApiInterface;
use Psr\Http\Message\StreamFactoryInterface;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Factory(new Options('https://foo.local'));
    }

    public function testItMakesTheServiceFactory(): void
    {
        $result = $this->subject->makeServiceFactory();

        $this->assertInstanceOf(ServiceFactory::class, $result);
    }

    public function testItMakesTheSerializer(): void
    {
        $result = $this->subject->makeSerializer();

        $this->assertInstanceOf(Serializer::class, $result);
    }

    public function testItMakesTheKindManager(): void
    {
        $result = $this->subject->makeKindManager();

        $this->assertInstanceOf(KindManager::class, $result);
    }

    public function testItMakesTheApi(): void
    {
        $result = $this->subject->makeApi();

        $this->assertInstanceOf(ApiInterface::class, $result);
    }

    public function testItMakesTheWebSocketClientFactory(): void
    {
        $result = $this->subject->makeWebsocketClientFactory();

        $this->assertInstanceOf(WebsocketClientFactory::class, $result);
    }

    public function testItMakesTheHttpClient(): void
    {
        $result = $this->subject->makeHttpClient();

        $this->assertInstanceOf(HttpClient::class, $result);
    }

    public function testItMakesTheRequestFactory(): void
    {
        $result = $this->subject->makeRequestFactory();

        $this->assertInstanceOf(RequestFactory::class, $result);
    }

    public function testItMakesTheUriBuilder(): void
    {
        $result = $this->subject->makeUriBuilder();

        $this->assertInstanceOf(UriBuilder::class, $result);
    }

    public function testItMakesTheMetadataCache(): void
    {
        $result = $this->subject->makeMetadataCache();

        $this->assertInstanceOf(MetadataCache::class, $result);
    }

    public function testItMakesTheNormalizer(): void
    {
        $result = $this->subject->makeNormalizer();

        $this->assertInstanceOf(NormalizerInterface::class, $result);
    }

    public function testItMakesTheDenormalizer(): void
    {
        $result = $this->subject->makeDenormalizer();

        $this->assertInstanceOf(DenormalizerInterface::class, $result);
    }

    public function testItMakesTheStreamFactory(): void
    {
        $result = $this->subject->makeStreamFactory();

        $this->assertInstanceOf(StreamFactoryInterface::class, $result);
    }

    public function testItMakesTheArchiveFactory(): void
    {
        $result = $this->subject->makeArchiveFactory();

        $this->assertInstanceOf(ArchiveFactory::class, $result);
    }

    public function testItMakesTheHttpClientFactory(): void
    {
        $result = $this->subject->makeHttpClientFactory();

        $this->assertInstanceOf(HttpClientFactory::class, $result);
    }

    public function testItMakesTheWebsocketAdapterFactory(): void
    {
        $result = $this->subject->makeWebsocketAdapterFactory();

        $this->assertInstanceOf(WebsocketAdapterFactory::class, $result);
    }

    public function testItMakesTheContextConfigFactory(): void
    {
        $result = $this->subject->makeContextConfigFactory();

        $this->assertInstanceOf(ContextConfigFactory::class, $result);
    }
}
