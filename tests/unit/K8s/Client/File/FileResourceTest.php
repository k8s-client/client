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

use K8s\Client\File\FileResource;
use unit\K8s\Client\TestCase;

class FileResourceTest extends TestCase
{
    /**
     * @var FileResource
     */
    private $subject;

    /**
     * @var string
     */
    private $file;

    public function setUp(): void
    {
        parent::setUp();
        $this->file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'k8s-client-file-test.txt';
        $this->subject = new FileResource($this->file);
    }

    public function testWrite(): void
    {
        if ($this->isWindowsPlatform()) {
            $this->markTestSkipped('Resource based tests are not reliable on Windows.');
        }
        $this->subject->write('foo');

        $this->assertEquals('foo', file_get_contents($this->file));
    }

    public function testDelete(): void
    {
        if ($this->isWindowsPlatform()) {
            $this->markTestSkipped('Resource based tests are not reliable on Windows.');
        }
        $this->subject->delete();

        $this->assertFalse(file_exists($this->file));
    }

    public function testClose(): void
    {
        if ($this->isWindowsPlatform()) {
            $this->markTestSkipped('Resource based tests are not reliable on Windows.');
        }
        $this->subject->write('foo');
        $this->subject->close();

        $this->assertTrue(file_exists($this->file));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        @unlink($this->file);
    }
}
