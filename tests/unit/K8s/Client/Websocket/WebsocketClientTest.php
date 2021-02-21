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
use K8s\Client\Websocket\FrameHandler\ExecHandler;
use K8s\Client\Websocket\FrameHandler\GenericHandler;
use K8s\Client\Websocket\FrameHandler\PortForwardHandler;
use K8s\Client\Websocket\WebsocketClient;
use K8s\Core\Exception\WebsocketException;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use Nyholm\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use unit\K8s\Client\TestCase;

class WebsocketClientTest extends TestCase
{
    /**
     * @var RequestFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $requestFactory;

    /**
     * @var WebsocketClientInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $adapter;

    /**
     * @var WebsocketClient
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->adapter = \Mockery::spy(WebsocketClientInterface::class);
        $this->requestFactory = \Mockery::spy(RequestFactory::class);
        $this->subject = new WebsocketClient(
            $this->adapter,
            $this->requestFactory
        );
    }

    public function testItUsesTheExecHandlerOnConnectForExec(): void
    {
        $request = new Request('GET', '/foo');
        $this->requestFactory->shouldReceive('makeRequest')
            ->andReturn($request);
        $callable = function () {};

        $this->subject->connect('/foo', 'exec', $callable);
        $this->adapter->shouldHaveReceived(
            'connect',
            [
                \Mockery::type(RequestInterface::class),
                \Mockery::type(ExecHandler::class)
            ]
        );
    }

    public function testItUsesThePortForwardHandlerOnConnectForPortForward(): void
    {
        $request = new Request('GET', '/foo?ports=80&ports=443');
        $this->requestFactory->shouldReceive('makeRequest')
            ->andReturn($request);
        $callable = function () {};

        $this->subject->connect('/foo', 'portforward', $callable);
        $this->adapter->shouldHaveReceived(
            'connect',
            [
                \Mockery::type(RequestInterface::class),
                \Mockery::type(PortForwardHandler::class)
            ]
        );
    }

    public function testItThrowsAnExceptionOnConnectForUnknownType(): void
    {
        $request = new Request('GET', '/foo');
        $this->requestFactory->shouldReceive('makeRequest')
            ->andReturn($request);
        $callable = function () {};

        $this->expectException(WebsocketException::class);
        $this->subject->connect('/foo', 'foo', $callable);
    }
}
