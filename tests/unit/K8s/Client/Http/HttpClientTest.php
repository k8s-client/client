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

use Http\Discovery\Psr17FactoryDiscovery;
use K8s\Client\Http\Contract\ResponseHandlerInterface;
use K8s\Client\Http\Exception\HttpException;
use K8s\Client\Http\HttpClient;
use K8s\Client\Http\HttpClientFactory;
use K8s\Client\Http\RequestFactory;
use K8s\Client\Http\ResponseHandlerFactory;
use K8s\Client\Serialization\Serializer;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use unit\K8s\Client\TestCase;

class HttpClientTest extends TestCase
{
    /**
     * @var RequestFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $requestFactory;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ClientInterface
     */
    private $client;

    /**
     * @var Serializer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $serializer;

    /**
     * @var ResponseHandlerFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $handlerFactory;

    /**
     * @var ResponseHandlerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $responseHandler;

    /**
     * @var HttpClient
     */
    private $subject;

    /**
     * @var HttpClientFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $clientFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->clientFactory = \Mockery::spy(HttpClientFactory::class);
        $this->requestFactory = \Mockery::spy(RequestFactory::class);
        $this->client = \Mockery::spy(ClientInterface::class);
        $this->serializer = \Mockery::spy(Serializer::class);
        $this->handlerFactory = \Mockery::spy(ResponseHandlerFactory::class);
        $this->responseHandler = \Mockery::spy(ResponseHandlerInterface::class);
        $this->handlerFactory->shouldReceive('makeHandlers')->andReturn([$this->responseHandler]);
        $this->clientFactory->shouldReceive('makeClient')->andReturn($this->client);
        $this->subject = new HttpClient(
            $this->requestFactory,
            $this->clientFactory,
            $this->serializer,
            $this->handlerFactory
        );
    }

    public function testItSerializesTheBodyIfItExists(): void
    {
        $this->serializer->expects('serialize')
            ->with(['foo' => 'bar'])
            ->andReturn('{}');

        $this->responseHandler->shouldReceive([
            'supports' => true,
            'handle' => 'bar',
        ]);

        $result = $this->subject->send('/foo', 'bar', ['body' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $result);
    }

    public function testItThrowsHttpExceptionIfNoHandlerWasFound(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(601);
        $response->shouldReceive('getReasonPhrase')
            ->andReturn('oh no');
        $this->client->shouldReceive('sendRequest')
            ->andReturn($response);

        $this->responseHandler->shouldReceive([
            'supports' => false,
        ]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('oh no');
        $this->subject->send('/foo', 'bar', []);
    }

    public function testItThrowsAnHttpExceptionIfTheClientExceptionIsThrown(): void
    {
        $exception = new class extends \Exception implements ClientExceptionInterface {
            public function __construct(string $message = "Network failure?.", int $code = 3, Throwable $previous = null)
            {
                parent::__construct($message, $code, $previous);
            }
        };

        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(401);
        $this->client->shouldReceive('sendRequest')
            ->andThrow($exception);

        $this->responseHandler->shouldReceive([
            'supports' => false,
        ]);

        $this->expectException(get_class($exception));
        $this->subject->send('/foo', 'bar', []);
    }

    public function testItCanSendProxyRequest(): void
    {
        $this->serializer->expects('serialize')
            ->never();

        $this->responseHandler->shouldReceive([
            'supports' => true,
            'handle' => \Mockery::spy(RequestInterface::class),
        ]);

        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $request = $requestFactory->createRequest('POST', '/foo');
        $request = $request->withBody($streamFactory->createStream(json_encode(['foo' => 'bar'])));

        $result = $this->subject->send('/foo', 'bar', ['proxy' => $request]);
        $this->assertInstanceOf(RequestInterface::class, $result);
    }
}
