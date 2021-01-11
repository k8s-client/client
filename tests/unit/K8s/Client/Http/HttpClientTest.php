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

use K8s\Client\Http\Contract\ResponseHandlerInterface;
use K8s\Client\Http\HttpClient;
use K8s\Client\Http\RequestFactory;
use K8s\Client\Http\ResponseHandlerFactory;
use K8s\Client\Serialization\Serializer;
use K8s\Core\Exception\HttpException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->requestFactory = \Mockery::spy(RequestFactory::class);
        $this->client = \Mockery::spy(ClientInterface::class);
        $this->serializer = \Mockery::spy(Serializer::class);
        $this->handlerFactory = \Mockery::spy(ResponseHandlerFactory::class);
        $this->responseHandler = \Mockery::spy(ResponseHandlerInterface::class);
        $this->handlerFactory->shouldReceive('makeHandlers')->andReturn([$this->responseHandler]);
        $this->subject = new HttpClient(
            $this->requestFactory,
            $this->client,
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

    public function testItThrowsAnHttpExceptionIfNotHandlerWasFound(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(401);
        $this->client->shouldReceive('sendRequest')
            ->andReturn($response);

        $this->responseHandler->shouldReceive([
            'supports' => false,
        ]);

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('There was no supported handler found for the API response to path: /foo');
        $this->subject->send('/foo', 'bar', []);
    }

    public function testItThrowsAnHttpExceptionIfTheClientExceptionIsThrown(): void
    {
        $exception = new class extends \Exception implements ClientExceptionInterface {
            public function __construct(string $message = "The HTTP Request Failed.", int $code = 500, Throwable $previous = null)
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

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('The HTTP Request Failed');
        $this->subject->send('/foo', 'bar', []);
    }
}
