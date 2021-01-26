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

use K8s\Client\File\Exception\FileDownloadException;
use K8s\Client\File\FileResource;
use K8s\Client\File\Handler\FileDownloadExecHandler;
use K8s\Client\Websocket\ExecConnection;
use unit\K8s\Client\TestCase;

class FileDownloadExecHandlerTest extends TestCase
{
    /**
     * @var FileResource|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $file;

    /**
     * @var FileDownloadExecHandler
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->file = \Mockery::spy(FileResource::class);
        $this->subject = new FileDownloadExecHandler($this->file);
    }

    public function testOnCloseItClosesTheFile(): void
    {
        $this->subject->onClose();

        $this->file->shouldHaveReceived('close');
    }

    public function testItWritesToTheFileWhenReceivingStdout(): void
    {
        $this->subject->onReceive(ExecConnection::CHANNEL_STDOUT, 'foo', \Mockery::spy(ExecConnection::class));

        $this->file->shouldHaveReceived('write', ['foo']);
    }

    public function testItThrowsAnExceptionIfStderrIsReceived(): void
    {
        $this->expectException(FileDownloadException::class);

        $this->subject->onReceive(ExecConnection::CHANNEL_STDERR, 'foo', \Mockery::spy(ExecConnection::class));
    }
}
