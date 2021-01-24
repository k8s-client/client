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
        $this->subject->addFromString('/foo.txt', 'data');
        $this->subject->delete();

        $this->assertFalse(file_exists($this->tmpFile));
    }

    public function testItIgnoresDeletingTheArchiveIfItDoesNotExist(): void
    {
        $this->subject->addFromString('/foo.txt', 'data');
        unlink($this->tmpFile);
        $this->subject->delete();

        $this->assertFalse(file_exists($this->tmpFile));
    }

    public function testTheStreamCannotBeCreatedWhenNoFileExists(): void
    {
        $this->expectException(FileException::class);

        $this->subject->toStream();
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

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }
    }
}
