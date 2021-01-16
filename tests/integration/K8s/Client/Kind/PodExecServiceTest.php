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

class PodExecServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $container = new Container('exec-test', 'busybox:latest');
        $container->setIsTty(true);
        $pod = new Pod(
            'exec-test',
            [$container]
        );
        $pod->setLabels(['app' => 'exec-test']);
        $this->createAndWaitForPod($pod);
    }

    public function testItRunsTheCommandAndReturnsResults(): void
    {
        $results = [];

        $this->k8s()->exec('exec-test', '/bin/whoami')
            ->useStdout()
            ->useStdin(false)
            ->run(function (string $channel, string $data) use (&$results) {
                if (!empty(trim($data))) {
                    $results[$channel] = $data;

                    return false;
                }
            });

        $this->assertArrayHasKey('stdout', $results);
        $this->assertStringContainsString('root', $results['stdout']);
    }

    public function testItRunsMultipleCommandStrings(): void
    {
        $results = [];

        $this->k8s()->exec('exec-test', ['/bin/ls', '-l'])
            ->useStdout()
            ->useStdin(false)
            ->run(function (string $channel, string $data) use (&$results) {
                if ($channel === 'stdout' && !empty(trim($data))) {
                    $results[$channel] = $data;

                    return false;
                }
            });

        $this->assertArrayHasKey('stdout', $results);
        $this->assertStringContainsStringIgnoringCase(' etc', $results['stdout']);
        $this->assertStringContainsStringIgnoringCase(' var', $results['stdout']);
    }
}
