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
use K8s\Client\Http\ResponseHandler\FollowHandler;
use K8s\Client\Serialization\Serializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use unit\K8s\Client\TestCase;

class FollowHandlerTest extends TestCase
{
    /**
     * @var Serializer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $serializer;

    /**
     * @var FollowHandler
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->serializer = \Mockery::spy(Serializer::class);
        $this->subject = new FollowHandler($this->serializer);
    }

    public function testItSupportsFollowTypeApiResponses(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->expects('getHeader')
            ->with('content-type')
            ->andReturn(['text/plain']);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $result = $this->subject->supports(
            $response,
            ['query' => ['follow' => true], 'handler' => function(){}]
        );
        $this->assertTrue($result);
    }

    public function testItDoesNotSupportQueriesWithoutFollow(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $result = $this->subject->supports(
            $response,
            ['query' => ['follow' => false], 'handler' => function(){}]
        );
        $this->assertFalse($result);
    }

    public function testItOnlySupportsPlainText(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->expects('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $result = $this->subject->supports(
            $response,
            ['query' => ['follow' => true], 'handler' => function(){}]
        );
        $this->assertFalse($result);
    }

    public function testItDoesNotSupportQueriesWithoutHandlers(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('When using follow in your query you must specify the "handler" callable parameter');
        $this->subject->supports(
            $response,
            ['query' => ['follow' => true]]
        );
    }

    public function testItHandlesFollowingTheResponse(): void
    {
        $stream = \Mockery::mock(StreamInterface::class);
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getBody')
            ->andReturn($stream);

        $stream->shouldReceive('read')
            ->andReturn('log 1', 'log 2');
        $stream->shouldReceive('eof')
            ->andReturn(false, true);
        $stream->shouldReceive('close');

        $object = new class { function __invoke(){} };
        $objectMocker = \Mockery::spy($object);

        $objectMocker->shouldReceive('__invoke')
            ->with('log 1');
        $objectMocker->shouldReceive('__invoke')
            ->with('log 2');

        $options = ['query' => ['follow' => true], 'handler' => $objectMocker];
        $this->subject->handle($response, $options);

        $stream->shouldHaveReceived('read');
        $objectMocker->shouldHaveReceived('__invoke');
    }
}
