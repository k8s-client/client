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

use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;
use K8s\Client\Exception\KubernetesException;
use K8s\Client\Http\ResponseHandler\ErrorHandler;
use K8s\Client\Serialization\Serializer;
use K8s\Core\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use unit\K8s\Client\TestCase;

class ErrorHandlerTest extends TestCase
{
    /**
     * @var Serializer|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $serializer;

    /**
     * @var ErrorHandler
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->serializer = \Mockery::spy(Serializer::class);
        $this->subject = new ErrorHandler($this->serializer);
    }

    public function testItDoesNotSupportWhenThereAreNoErrors(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $this->assertFalse($this->subject->supports($response, []));
    }

    public function testItDoesSupportWhenThereAreServerErrors(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(504);

        $this->assertTrue($this->subject->supports($response, []));
    }

    public function testItDoesSupportWhenThereAreClientErrors(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(400);

        $this->assertTrue($this->subject->supports($response, []));
    }

    public function testItThrowsStandardHttpExceptionIfTheContentIsNotJson(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(500);
        $response->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn([]);
        $response->shouldReceive('getReasonPhrase')
            ->andReturn('stuff broke');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('stuff broke');
        $this->expectExceptionCode(500);
        $this->subject->handle($response, []);
    }

    public function testItThrowsKubernetesExceptionIfTheContentIsJson(): void
    {
        $response = \Mockery::spy(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(500);
        $response->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);
        $response->shouldReceive('getReasonPhrase')
            ->andReturn('stuff broke');

        $stream = \Mockery::spy(StreamInterface::class);
        $stream->shouldReceive('__toString')
            ->andReturn('{"foo":"bar"}');
        $response->shouldReceive('getBody')
            ->andReturn($stream);

        $status = \Mockery::spy(Status::class);
        $this->serializer->shouldReceive('deserialize')
            ->andReturn($status);
        $status->shouldReceive([
            'getMessage' => 'Stuff is still broken',
            'getCode' => 500,
        ]);

        $this->expectException(KubernetesException::class);
        $this->expectExceptionMessage('Stuff is still broken');
        $this->expectExceptionCode(500);
        $this->subject->handle($response, []);
    }
}
