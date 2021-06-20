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

namespace unit\K8s\Client;

use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\File\FileDownloader;
use K8s\Client\File\FileUploader;
use K8s\Client\K8s;
use K8s\Client\Kind\PodAttachService;
use K8s\Client\Kind\PodExecService;
use K8s\Client\Kind\PodLogService;
use K8s\Client\Kind\PortForwardService;
use K8s\Client\Options;

class K8sTest extends TestCase
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var K8s
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->options = new Options('https://foo');
        $this->subject = new K8s($this->options);
    }

    public function testLogsReturnsLogClass(): void
    {
        $result = $this->subject->logs('foo');

        $this->assertInstanceOf(PodLogService::class, $result);
    }

    public function testExecReturnsExecClass(): void
    {
        $result = $this->subject->exec('foo');

        $this->assertInstanceOf(PodExecService::class, $result);
    }

    public function testAttachReturnsAttachClass(): void
    {
        $result = $this->subject->attach('foo');

        $this->assertInstanceOf(PodAttachService::class, $result);
    }

    public function testFileUploaderReturnsFileUploaderClass(): void
    {
        $result = $this->subject->uploader('foo');

        $this->assertInstanceOf(FileUploader::class, $result);
    }

    public function testFileDownloaderReturnsFileDownloaderClass(): void
    {
        $result = $this->subject->downloader('foo');

        $this->assertInstanceOf(FileDownloader::class, $result);
    }

    public function testPortForwardReturnsPortForwardClass(): void
    {
        $result = $this->subject->portforward('foo', 80);

        $this->assertInstanceOf(PortForwardService::class, $result);
    }

    public function testPortForwardReturnsPortForwardClassWithMultiplePorts(): void
    {
        $result = $this->subject->portforward('foo', [80, 443]);

        $this->assertInstanceOf(PortForwardService::class, $result);
    }

    public function testItCreatesTheNewKindFromArrayData(): void
    {
        $result = $this->subject->newKind([
            'apiVersion' => 'v1',
            'kind' => 'Pod',
            'metadata' => [
                'name' => 'foo',
            ],
            'spec' => [
                'containers' => [
                    [
                        'image' => 'nginx:latest',
                        'name' => 'web',
                    ],
                ]
            ],
        ]);

        $this->assertInstanceOf(Pod::class, $result);
    }

    public function testGetOptions(): void
    {
        $this->assertEquals($this->options, $this->subject->getOptions());
    }
}
