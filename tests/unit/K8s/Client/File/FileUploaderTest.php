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

namespace unit\K8s\Client\File;

use K8s\Client\File\ArchiveFactory;
use K8s\Client\File\Contract\ArchiveInterface;
use K8s\Client\File\Exception\FileUploadException;
use K8s\Client\File\FileUploader;
use K8s\Client\File\Handler\FileUploadExecHandler;
use K8s\Client\Kind\PodExecService;
use unit\K8s\Client\TestCase;

class FileUploaderTest extends TestCase
{
    /**
     * @var PodExecService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $execService;

    /**
     * @var ArchiveFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $archiveFactory;

    /**
     * @var FileUploader
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->archiveFactory = \Mockery::spy(ArchiveFactory::class);
        $this->execService = \Mockery::spy(PodExecService::class);
        $this->subject = new FileUploader(
            $this->archiveFactory,
            $this->execService
        );
    }

    public function testItCanAddFromFile(): void
    {
        $archive = \Mockery::spy(ArchiveInterface::class);
        $this->archiveFactory->shouldReceive('makeArchive')
            ->andReturn($archive);

        $archive->shouldReceive('addFile')
            ->with('/foo.txt', '/bar.txt');

        $this->subject->addFile('/foo.txt', '/bar.txt');
        $archive->shouldHaveReceived('addFile');
    }

    public function testItCanAddFromString(): void
    {
        $archive = \Mockery::spy(ArchiveInterface::class);
        $this->archiveFactory->shouldReceive('makeArchive')
            ->andReturn($archive);

        $archive->shouldReceive('addFromString')
            ->with('/foo.txt', 'data');

        $this->subject->addFileFromString('/foo.txt', 'data');
        $archive->shouldHaveReceived('addFromString');
    }

    public function testItRunsTheCommandToUpload(): void
    {
        $archive = \Mockery::spy(ArchiveInterface::class);
        $this->archiveFactory->shouldReceive('makeArchive')
            ->andReturn($archive);

        $this->execService->shouldReceive('useStdout')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('useStdin')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('command')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('useTty')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('useStderr')
            ->andReturn($this->execService);

        $this->subject->addFileFromString('/foo.txt', 'data');
        $this->subject->upload();
        $this->execService->shouldHaveReceived('run', [\Mockery::type(FileUploadExecHandler::class)]);
    }

    public function testItRunsTheCommandToUploadInSpecificContainer(): void
    {
        $archive = \Mockery::spy(ArchiveInterface::class);
        $this->archiveFactory->shouldReceive('makeArchive')
            ->andReturn($archive);

        $this->execService->shouldReceive('useStdout')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('useStdin')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('command')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('useTty')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('useStderr')
            ->andReturn($this->execService);
        $this->execService->shouldReceive('useContainer')
            ->with('foo')
            ->andReturn($this->execService);

        $this->subject->useContainer('foo');
        $this->subject->addFileFromString('/foo.txt', 'data');
        $this->subject->upload();
        $this->execService->shouldHaveReceived('run', [\Mockery::type(FileUploadExecHandler::class)]);
    }

    public function testItThrowsAnExceptionIfNoFilesWereAddedToUpload()
    {
        $this->expectException(FileUploadException::class);

        $this->subject->upload();
    }
}
