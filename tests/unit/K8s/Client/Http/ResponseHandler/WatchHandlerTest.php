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

namespace unit\K8s\Client\Http\ResponseHandler;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\Http\ResponseHandler\WatchHandler;
use K8s\Client\Serialization\Serializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use unit\K8s\Client\TestCase;

class WatchHandlerTest extends TestCase
{
    /**
     * @var Serializer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $serializer;

    /**
     * @var WatchHandler
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->serializer = \Mockery::spy(Serializer::class);
        $this->subject = new WatchHandler($this->serializer);
    }

    public function testItSupportsWatchTypeApiResponses(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->expects('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $result = $this->subject->supports(
            $response,
            ['query' => ['watch' => true], 'handler' => function(){}]
        );
        $this->assertTrue($result);
    }

    public function testItDoesNotSupportQueriesWithoutWatch(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $result = $this->subject->supports(
            $response,
            ['query' => ['watch' => false], 'handler' => function(){}]
        );
        $this->assertFalse($result);
    }

    public function testItDoesNotSupportQueriesWithoutHandlers(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('When using watch in your query you must specify the "handler" callable parameter');
        $this->subject->supports(
            $response,
            ['query' => ['watch' => true]]
        );
    }

    public function testItHandlesWatchingTheResponse(): void
    {
        $stream = \Mockery::mock(StreamInterface::class);
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getBody')
            ->andReturn($stream);

        $stream->shouldReceive('read')
            ->andReturn('{"foo":"bar"}');
        $stream->shouldReceive('eof')
            ->andReturn(false, true);
        $stream->shouldReceive('rewind')
            ->andReturnNull();
        $stream->shouldReceive('close');

        $object = new class { function __invoke(){} };
        $objectMocker = \Mockery::spy($object);

        $watchObj = new \stdClass();
        $this->serializer->shouldReceive('deserialize')
            ->with('{"foo":"bar"}')
            ->andReturn($watchObj);

        $objectMocker->shouldReceive('__invoke')
            ->with($watchObj);

        $options = ['query' => ['watch' => true], 'handler' => $objectMocker];
        $this->subject->handle($response, $options);

        $stream->shouldHaveReceived('read');
        $this->serializer->shouldHaveReceived('deserialize');
        $objectMocker->shouldHaveReceived('__invoke');
    }
}
