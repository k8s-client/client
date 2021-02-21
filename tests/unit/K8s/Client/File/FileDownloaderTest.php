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
use K8s\Client\File\FileDownloader;
use K8s\Client\Kind\PodExecService;
use unit\K8s\Client\TestCase;

class FileDownloaderTest extends TestCase
{
    /**
     * @var PodExecService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $exec;

    /**
     * @var ArchiveFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $archiveFactory;

    /**
     * @var FileDownloader
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->exec = \Mockery::spy(PodExecService::class);
        $this->archiveFactory = \Mockery::spy(ArchiveFactory::class);
        $this->subject = new FileDownloader(
            $this->exec,
            $this->archiveFactory
        );
    }

    public function testItDownloads(): void
    {
        $this->exec->shouldReceive('useStdout')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useStderr')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useStdin')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('command')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useTty')
            ->andReturn($this->exec);

        $this->subject->from('/etc');
        $result = $this->subject->download();

        $expectedCommand = [
            "tar",
            "cf",
            "-",
            "-C",
            "/",
            "etc",
        ];

        $this->assertInstanceOf(ArchiveInterface::class, $result);
        $this->exec->shouldHaveReceived('command', [$expectedCommand]);
    }

    public function testItDownloadsWithCompression(): void
    {
        $this->exec->shouldReceive('useStdout')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useStderr')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useStdin')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('command')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useTty')
            ->andReturn($this->exec);

        $this->subject->compress();
        $this->subject->from('/etc');
        $result = $this->subject->download();

        $expectedCommand = [
            "tar",
            "czf",
            "-",
            "-C",
            "/",
            "etc",
        ];

        $this->assertInstanceOf(ArchiveInterface::class, $result);
        $this->exec->shouldHaveReceived('command', [$expectedCommand]);
    }

    public function testItDownloadsToSpecificFileWithContainer(): void
    {
        $this->exec->shouldReceive('useStdout')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useStderr')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useStdin')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('command')
            ->andReturn($this->exec);
        $this->exec->shouldReceive('useTty')
            ->andReturn($this->exec);

        $to = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-to.tar';
        $this->subject->from('/etc');
        $this->subject->to($to);
        $this->subject->useContainer('meh');
        $result = $this->subject->download();

        $expectedCommand = [
            "tar",
            "cf",
            "-",
            "-C",
            "/",
            "etc",
        ];

        $this->assertInstanceOf(ArchiveInterface::class, $result);
        $this->exec->shouldHaveReceived('command', [$expectedCommand]);
        $this->exec->shouldHaveReceived('useContainer', ['meh']);
    }
}
