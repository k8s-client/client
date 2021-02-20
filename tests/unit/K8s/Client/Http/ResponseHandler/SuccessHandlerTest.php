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

use K8s\Client\Http\ResponseHandler\SuccessHandler;
use K8s\Client\Serialization\Serializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use unit\K8s\Client\TestCase;

class SuccessHandlerTest extends TestCase
{
    /**
     * @var Serializer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $serializer;

    /**
     * @var SuccessHandler
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->serializer = \Mockery::spy(Serializer::class);
        $this->subject = new SuccessHandler($this->serializer);
    }

    public function testItSupportsSuccessfulResponses(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $this->assertTrue($this->subject->supports($response, []));
    }

    public function testItDoesNotSupportUnsuccessfulResponses(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(500);

        $this->assertFalse($this->subject->supports($response, []));
    }

    public function testItDeserializesJsonContent(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);

        $stream = \Mockery::spy(StreamInterface::class);
        $stream->shouldReceive('__toString')
            ->andReturn('{"foo":"bar"}');
        $response->shouldReceive('getBody')
            ->andReturn($stream);

        $model = new \stdClass();
        $this->serializer->shouldReceive('deserialize')
            ->andReturn($model);

        $successResponse = $this->subject->handle($response, ['model' => 'foo']);
        $this->assertEquals($model, $successResponse);
        $this->serializer->shouldHaveReceived('deserialize');
    }

    public function testItReturnsTheRawResultIfThereIsNoJson(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn([]);

        $stream = \Mockery::spy(StreamInterface::class);
        $stream->shouldReceive('__toString')
            ->andReturn('stuff');
        $response->shouldReceive('getBody')
            ->andReturn($stream);

        $successResponse = $this->subject->handle($response, ['model' => 'foo']);
        $this->assertEquals('stuff', $successResponse);
        $this->serializer->shouldNotHaveReceived('deserialize');
    }

    public function testItReturnsTheResponseClassIfItIsProxyRequest(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $options['proxy'] = true;

        $result = $this->subject->handle($response, $options);
        $this->assertEquals($response, $result);
    }

}
