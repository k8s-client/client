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

    public function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'k8s-extract';
        mkdir($this->tmpDir);
        $this->createAndWaitForPod(new Pod(
            'test-copy',
            [new Container('test-copy', 'nginx:latest')]
        ));
    }

    public function tearDown(): void
    {
        parent::tearDown();
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
            ->fileDownloader('test-copy')
            ->from('/etc')
            ->download();
        $result->extractTo($this->tmpDir);

        $this->assertGreaterThan(0, $result->getSize());
        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue(file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'etc'));
        @unlink($result->getRealPath());
    }

    public function testItCanDownloadFilesFromThePodToSpecificFile(): void
    {
        $archive = __DIR__ . DIRECTORY_SEPARATOR . 'archive.tar';
        $result = $this->k8s()
            ->fileDownloader('test-copy')
            ->from('/etc')
            ->toFile($archive)
            ->download();
        $result->extractTo($this->tmpDir);

        $this->assertGreaterThan(0, $result->getSize());
        $this->assertGreaterThan(0, $result->count());
        $this->assertEquals($archive, $result->getRealPath());
        $this->assertTrue(file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'etc'));
        @unlink($result->getRealPath());
    }

    public function testItCanDownloadCompressedFilesFromThePod(): void
    {
        $result = $this->k8s()
            ->fileDownloader('test-copy')
            ->compress()
            ->from('/etc')
            ->download();
        $result->extractTo($this->tmpDir);

        $this->assertGreaterThan(0, $result->getSize());
        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue(file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'etc'));
        $this->assertStringEndsWith('.tar.gz', $result->getRealPath());
        @unlink($result->getRealPath());
    }
}
