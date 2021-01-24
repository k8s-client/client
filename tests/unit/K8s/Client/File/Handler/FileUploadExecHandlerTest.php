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

namespace unit\K8s\Client\File\Handler;

use K8s\Client\File\Exception\FileUploadException;
use K8s\Client\File\Handler\FileUploadExecHandler;
use K8s\Client\Websocket\ExecConnection;
use Psr\Http\Message\StreamInterface;
use unit\K8s\Client\TestCase;

class FileUploadExecHandlerTest extends TestCase
{
    /**
     * @var FileUploadExecHandler
     */
    private $subject;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|StreamInterface
     */
    private $stream;

    public function setUp(): void
    {
        parent::setUp();
        $this->stream = \Mockery::spy(StreamInterface::class);
        $this->subject = new FileUploadExecHandler($this->stream);
    }

    public function testItWritesDataToTheStreamOnceItIsOpen(): void
    {
        $this->stream->shouldReceive('eof')
            ->andReturn(false, false, true);
        $this->stream->shouldReceive('read')
            ->andReturn('foo', 'bar');

        $connection = \Mockery::spy(ExecConnection::class);

        $this->subject->onOpen($connection);
        $connection->shouldHaveReceived('write');
        $this->stream->shouldHaveReceived('read');
        $connection->shouldHaveReceived('close');
    }

    public function testItThrowsAnExceptionIfStderrIsReceived(): void
    {
        $connection = \Mockery::spy(ExecConnection::class);

        $this->expectException(FileUploadException::class);
        $this->expectExceptionMessageMatches('/Something broke/');
        $this->subject->onReceive(ExecConnection::CHANNEL_STDERR, 'Something broke.', $connection);
        $connection->shouldHaveReceived('close');
    }

    public function testItDoesNotThrowAnExceptionIfStderrIsNotReceived(): void
    {
        $connection = \Mockery::spy(ExecConnection::class);

        $this->subject->onReceive(ExecConnection::CHANNEL_STDOUT, 'Oh, hi Mark.', $connection);
        $connection->shouldNotHaveReceived('close');
    }
}
