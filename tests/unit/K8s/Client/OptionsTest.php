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

use Http\Discovery\Psr17FactoryDiscovery;
use K8s\Client\Options;
use K8s\Core\Contract\HttpClientFactoryInterface;
use K8s\Core\Contract\WebsocketClientFactoryInterface;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

class OptionsTest extends TestCase
{
    /**
     * @var Options
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Options(
            'https://foo.local/'
        );
    }

    public function testGetEndpoint(): void
    {
        $this->assertEquals('https://foo.local/', $this->subject->getEndpoint());
    }

    public function testGetNamespaceIsDefaultByDefault(): void
    {
        $this->assertEquals('default', $this->subject->getNamespace());
    }

    public function testGetAuthTypeIsTokenByDefault(): void
    {
        $this->assertEquals('token', $this->subject->getAuthType());
    }

    public function testGetSetHttpClient(): void
    {
        $client = \Mockery::spy(ClientInterface::class);
        $this->subject->setHttpClient($client);

        $this->assertEquals($client, $this->subject->getHttpClient());
    }

    public function testGetSetCache(): void
    {
        $cache = \Mockery::spy(CacheInterface::class);
        $this->subject->setCache($cache);

        $this->assertEquals($cache, $this->subject->getCache());
    }

    public function testGetSetWebSocket(): void
    {
        $websocket = \Mockery::spy(WebsocketClientInterface::class);
        $this->subject->setWebsocketClient($websocket);

        $this->assertEquals($websocket, $this->subject->getWebsocketClient());
    }

    public function testGetSetToken(): void
    {
        $this->subject->setToken('foo');

        $this->assertEquals('foo', $this->subject->getToken());
    }

    public function testGetSetUsername(): void
    {
        $this->subject->setUsername('foo');

        $this->assertEquals('foo', $this->subject->getUsername());
    }

    public function testGetSetPassword(): void
    {
        $this->subject->setPassword('foo');

        $this->assertEquals('foo', $this->subject->getPassword());
    }

    public function testGetSetHttpUriFactory(): void
    {
        $uriFactory = Psr17FactoryDiscovery::findUriFactory();

        $this->subject->setHttpUriFactory($uriFactory);
        $this->assertEquals($uriFactory, $this->subject->getHttpUriFactory());
    }

    public function testGetSetEndpoint(): void
    {
        $this->subject->setEndpoint('https://foo');
        $this->assertEquals('https://foo', $this->subject->getEndpoint());
    }

    public function testGetSetNamespace(): void
    {
        $this->subject->setNamespace('meh');
        $this->assertEquals('meh', $this->subject->getNamespace());
    }

    public function testGetSetHttpRequestFactory(): void
    {
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();

        $this->subject->setHttpRequestFactory($requestFactory);
        $this->assertEquals($requestFactory, $this->subject->getHttpRequestFactory());
    }

    public function testGetSetStreamFactory(): void
    {
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

        $this->subject->setStreamFactory($streamFactory);
        $this->assertEquals($streamFactory, $this->subject->getStreamFactory());
    }

    public function testGetSetWebsocketClientFactory(): void
    {
        $factory = \Mockery::spy(WebsocketClientFactoryInterface::class);

        $this->subject->setWebsocketClientFactory($factory);
        $this->assertEquals($factory, $this->subject->getWebsocketClientFactory());
    }

    public function testGetSetHttpClientFactory(): void
    {
        $factory = \Mockery::spy(HttpClientFactoryInterface::class);

        $this->subject->setHttpClientFactory($factory);
        $this->assertEquals($factory, $this->subject->getHttpClientFactory());
    }
}
