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

use K8s\Client\Http\RequestFactory;
use K8s\Client\Websocket\WebsocketAdapterFactory;
use K8s\Client\Websocket\WebsocketClient;
use K8s\Client\Websocket\WebsocketClientFactory;
use unit\K8s\Client\TestCase;

class WebsocketClientFactoryTest extends TestCase
{
    /**
     * @var WebsocketAdapterFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $adapterFactory;

    /**
     * @var WebsocketClientFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->adapterFactory = \Mockery::spy(WebsocketAdapterFactory::class);
        $this->subject = new WebsocketClientFactory(
            $this->adapterFactory,
            \Mockery::spy(RequestFactory::class)
        );
    }

    public function testMakeClient(): void
    {
        $result = $this->subject->makeClient();

        $this->assertInstanceOf(WebsocketClient::class, $result);
    }
}
