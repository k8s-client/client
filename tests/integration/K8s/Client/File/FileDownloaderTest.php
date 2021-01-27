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

namespace integration\K8s\Client\File;

use FilesystemIterator;
use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileDownloaderTest extends TestCase
{
    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $tmpFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'k8s-extract';
        $this->tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'k8s-file.tar';
        mkdir($this->tmpDir);
        $this->createAndWaitForPod(new Pod(
            'test-copy',
            [new Container('test-copy', 'nginx:latest')]
        ));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }
        if (!file_exists($this->tmpDir)) {
            return;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tmpDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        @rmdir($this->tmpDir);
    }

    public function testItCanDownloadFilesFromThePod(): void
    {
        $result = $this->k8s()
            ->downloader('test-copy')
            ->from('/etc')
            ->download();

        $this->assertGreaterThan(0, filesize($result->getRealPath()));
        $result->extractTo($this->tmpDir);
        $this->assertGreaterThan(0, count(glob($this->tmpDir . DIRECTORY_SEPARATOR . "*")));
        $this->assertTrue(file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'etc'));
    }

    public function testItCanDownloadFilesFromThePodToSpecificFile(): void
    {
        $archive = __DIR__ . DIRECTORY_SEPARATOR . 'archive.tar';
        $result = $this->k8s()
            ->downloader('test-copy')
            ->from('/etc')
            ->to($archive)
            ->download();

        $this->assertGreaterThan(0, filesize($result->getRealPath()));
        $this->assertEquals($archive, $result->getRealPath());
        $result->extractTo($this->tmpDir);
        $this->assertGreaterThan(0, count(glob($this->tmpDir . DIRECTORY_SEPARATOR . "*")));
        $this->assertTrue(file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'etc'));
    }

    public function testItCanDownloadCompressedFilesFromThePod(): void
    {
        $result = $this->k8s()
            ->downloader('test-copy')
            ->compress()
            ->from('/etc')
            ->download();

        $this->assertGreaterThan(0, filesize($result->getRealPath()));
        $this->assertStringEndsWith('.tar.gz', $result->getRealPath());
        $result->extractTo($this->tmpDir);
        $this->assertGreaterThan(0, count(glob($this->tmpDir . DIRECTORY_SEPARATOR . "*")));
        $this->assertTrue(file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'etc'));
    }
}
