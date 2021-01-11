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

use K8s\Client\Http\Api;
use K8s\Client\Http\HttpClient;
use K8s\Client\Http\UriBuilder;
use K8s\Client\Websocket\WebsocketClient;
use K8s\Client\Websocket\WebsocketClientFactory;
use unit\K8s\Client\TestCase;

class ApiTest extends TestCase
{
    /**
     * @var HttpClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $httpClient;

    /**
     * @var WebsocketClientFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $websocketFactory;

    /**
     * @var UriBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $uriBuilder;

    /**
     * @var Api
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->httpClient = \Mockery::spy(HttpClient::class);
        $this->websocketFactory = \Mockery::spy(WebsocketClientFactory::class);
        $this->uriBuilder = \Mockery::spy(UriBuilder::class);
        $this->subject = new Api(
            $this->httpClient,
            $this->websocketFactory,
            $this->uriBuilder
        );
    }

    public function testExecuteHttp(): void
    {
        $this->httpClient->shouldReceive('send')
            ->with('/foo', 'post', [])
            ->andReturn('meh');

        $result = $this->subject->executeHttp('/foo', 'post', []);
        $this->assertEquals('meh', $result);
    }

    public function testExecuteWebsocket(): void
    {
        $ws = \Mockery::spy(WebsocketClient::class);
        $this->websocketFactory->shouldReceive('makeClient')
            ->andReturn($ws);

        $ws->shouldReceive('connect')
            ->with('/foo', 'exec', []);

        $this->subject->executeWebsocket('/foo', 'exec', []);
    }

    public function testMakeUri(): void
    {
        $this->uriBuilder->shouldReceive('buildUri')
            ->with('/foo', [], [], 'meh')
            ->andReturn('yay');
        $result = $this->subject->makeUri('/foo', [], [], 'meh');

        $this->assertEquals('yay', $result);
    }
}
