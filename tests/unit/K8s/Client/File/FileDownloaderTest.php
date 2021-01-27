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
     * @var FileDownloader
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->exec = \Mockery::spy(PodExecService::class);
        $this->subject = new FileDownloader($this->exec);
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

        $this->assertInstanceOf(\PharData::class, $result);
        $this->exec->shouldHaveReceived('command', [$expectedCommand]);
    }
}