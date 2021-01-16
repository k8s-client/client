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

namespace integration\K8s\Client\Kind;

use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;

class PodLogServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createAndWaitForPod(new Pod(
            'logs-test',
            [new Container('logs-test', 'nginx:latest')]
        ));
    }

    public function testItCanReadtTheLogs(): void
    {
        $result = $this->k8s()
            ->logs('logs-test')
            ->makePretty()
            ->withTimestamps()
            ->read();

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('docker-entrypoint.sh', $result);
    }

    public function testItCanFollowLogs(): void
    {
        $results = [];

        $this->k8s()
            ->logs('logs-test')
            ->makePretty()
            ->withTimestamps()
            ->sinceSeconds(2400)
            ->follow(function (string $data) use (&$results) {
                $results[] = $data;

                return false;
            });

        $this->assertNotEmpty($results);
        $this->assertStringContainsString('docker-entrypoint.sh', implode('', $results));
    }
}
