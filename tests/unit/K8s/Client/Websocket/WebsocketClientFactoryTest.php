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

namespace unit\K8s\Client\Websocket;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\Http\RequestFactory;
use K8s\Client\Websocket\WebsocketClient;
use K8s\Client\Websocket\WebsocketClientFactory;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use unit\K8s\Client\TestCase;

class WebsocketClientFactoryTest extends TestCase
{
    /**
     * @var WebsocketClientFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new WebsocketClientFactory(null, \Mockery::spy(RequestFactory::class));
    }

    public function testMakeClient(): void
    {
        $result = $this->subject->makeClient();

        $this->assertInstanceOf(WebsocketClient::class, $result);
    }

    public function testMakeClientWithSpecificAdapter(): void
    {
        $client = \Mockery::spy(WebsocketClientInterface::class);
        $this->subject = new WebsocketClientFactory($client, \Mockery::spy(RequestFactory::class));

        $result = $this->subject->makeClient();

        $this->assertInstanceOf(WebsocketClient::class, $result);
    }

    public function testMakeClientWithNoAdapter(): void
    {
        $this->subject = new WebsocketClientFactory(
            null,
            \Mockery::spy(RequestFactory::class),
            []
        );

        $this->expectException(RuntimeException::class);
        $this->subject->makeClient();}
}
