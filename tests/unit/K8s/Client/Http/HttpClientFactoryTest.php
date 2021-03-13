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

namespace unit\K8s\Client\Http;

use K8s\Client\Http\HttpClientFactory;
use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Core\Contract\HttpClientFactoryInterface;
use Psr\Http\Client\ClientInterface;
use unit\K8s\Client\TestCase;

class HttpClientFactoryTest extends TestCase
{
    /**
     * @var HttpClientFactory
     */
    private $subject;

    /**
     * @var ContextConfigFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $configFactory;

    /**
     * @var HttpClientFactoryInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $httpClientFactory;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ClientInterface
     */
    private $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->configFactory = \Mockery::spy(ContextConfigFactory::class);
        $this->client = \Mockery::spy(ClientInterface::class);
        $this->httpClientFactory = \Mockery::spy(HttpClientFactoryInterface::class);
        $this->subject = new HttpClientFactory(
            $this->configFactory,
            null,
            $this->httpClientFactory
        );
    }

    public function testItCanMakeTheClientWhenItWasAlreadySet(): void
    {
        $this->subject = new HttpClientFactory(
            $this->configFactory,
            $this->client,
            $this->httpClientFactory
        );

        $this->assertEquals($this->client, $this->subject->makeClient(false));
    }

    public function testItCanMakeTheClientFromTheUserSuppliedClientFactory(): void
    {
        $this->subject->makeClient(false);

        $this->httpClientFactory->shouldHaveReceived('makeClient');
    }

    public function testItCanMakeTheClientFromDiscovery(): void
    {
        $this->subject = new HttpClientFactory(
            $this->configFactory,
            null,
            null
        );

        $this->assertInstanceOf(ClientInterface::class, $this->subject->makeClient(false));
    }
}
