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

namespace unit\K8s\Client\File\Archive;

use Http\Discovery\Psr17FactoryDiscovery;
use K8s\Client\File\Archive\Tar;
use K8s\Client\File\Exception\FileException;
use Psr\Http\Message\StreamInterface;
use unit\K8s\Client\TestCase;

class TarTest extends TestCase
{
    /**
     * @var string
     */
    private $tmpFile;

    /**
     * @var Tar
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-k8s-client.tar';
        $this->subject = new Tar($this->tmpFile, Psr17FactoryDiscovery::findStreamFactory());
    }

    public function testItCreatesTheArchiveFileUsingStringData(): void
    {
        $this->subject->addFromString('/foo.txt', 'data');

        $this->assertTrue(file_exists($this->tmpFile));
    }

    public function testItCreatesTheArchiveFileUsingFileData(): void
    {
        $this->subject->addFile(__DIR__ . DIRECTORY_SEPARATOR . 'TarTest.php', '/tmp/PhpFile.php');

        $this->assertTrue(file_exists($this->tmpFile));
    }

    public function testItReturnsTheArchiveStream(): void
    {
        $this->subject->addFromString('/foo.txt', 'data');
        $result = $this->subject->toStream();

        $this->assertInstanceOf(StreamInterface::class, $result);
    }

    public function testItDeletesTheArchiveIfItExists(): void
    {
        if ($this->isWindowsPlatform()) {
            $this->markTestSkipped('The Phar archive has sporadic timing issues on Windows.');
        }
        $this->subject->addFromString('/foo.txt', 'data');
        $this->subject->delete();

        $this->assertFalse(file_exists($this->tmpFile));
    }

    public function testItIgnoresDeletingTheArchiveIfItDoesNotExist(): void
    {
        if ($this->isWindowsPlatform()) {
            $this->markTestSkipped('The Phar archive has sporadic timing issues on Windows.');
        }
        $this->subject->addFromString('/foo.txt', 'data');
        unlink($this->tmpFile);
        $this->subject->delete();

        $this->assertFalse(file_exists($this->tmpFile));
    }

    public function testItHasTheCorrectUploadCommand(): void
    {
        $expected = [
            'tar',
            'xf',
            '-',
            '-C',
            '/',
        ];

        $this->assertEquals($expected, $this->subject->getUploadCommand());
    }

    public function testGetRealPath(): void
    {
        $this->assertEquals(
            $this->tmpFile,
            $this->subject->getRealPath()
        );
    }

    public function testToString(): void
    {
        $this->assertEquals(
            $this->tmpFile,
            (string)$this->subject
        );
    }

    public function testExtractTo(): void
    {
        $this->subject->addFromString('foo', 'bar');
        $to = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-extract';
        $this->subject->extractTo($to);
        $this->assertDirectoryExists($to);
    }

    public function testItThrowsExceptionOnToStreamWhenTheArchiveDoesntExistAnymore(): void
    {
        if ($this->isWindowsPlatform()) {
            $this->markTestSkipped('Resource based tests are not reliable on Windows.');
        }
        $this->expectException(FileException::class);

        $this->subject->toStream();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }
        $to = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-extract';
        if (is_dir($to)) {
            $this->deleteDir($to);
        }
    }

    private function deleteDir(string $dir): void
    {
        if (substr($dir, strlen($dir) - 1, 1) != '/') {
            $dir .= '/';
        }
        $files = glob($dir . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }
}
