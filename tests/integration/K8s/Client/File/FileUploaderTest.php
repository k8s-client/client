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

use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;

class FileUploaderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createAndWaitForPod(new Pod(
            'test-copy',
            [new Container('test-copy', 'nginx:latest')]
        ));
    }

    public function testItCanUploadFilesToThePod(): void
    {
        $this->k8s()
            ->uploader('test-copy')
            ->addFileFromString('/tmp/copy-test.txt', 'fake-data')
            ->upload();

        $results = [];

        $this->k8s()->exec('test-copy', ['/bin/ls', '-l', '/tmp'])
            ->useStdout()
            ->useStdin(false)
            ->run(function (string $channel, string $data) use (&$results) {
                if ($channel === 'stdout' && !empty(trim($data))) {
                    $results[$channel] = $data;

                    return false;
                }
            });

        $this->assertStringContainsString('copy-test.txt', implode('', $results));
    }
}
