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
use Psr\Http\Message\StreamFactoryInterface;
use unit\K8s\Client\TestCase;

class ArchiveFactoryTest extends TestCase
{
    /**
     * @var string
     */
    private $tmpFile;

    /**
     * @var ArchiveFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-k8s-client.tar';
        $this->subject = new ArchiveFactory(\Mockery::spy(StreamFactoryInterface::class));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }
    }

    public function testItMakesAnArchive(): void
    {
        $result = $this->subject->makeArchive($this->tmpFile);

        $this->assertInstanceOf(ArchiveInterface::class, $result);
    }
}
