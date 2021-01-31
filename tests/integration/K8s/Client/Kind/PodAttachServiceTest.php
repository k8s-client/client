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

class PodAttachServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $container = (new Container('attach-test', 'busybox:latest'))
            ->setCommand(['/bin/sh'])
            ->setArgs([
                '-c',
                'while(true); do echo hi; sleep 1; done',
            ]);
        $pod = new Pod(
            'attach-test',
            [$container]
        );
        $pod->setLabels(['app' => 'attach-test']);
        $this->createAndWaitForPod($pod);
    }

    public function testItAttachesAndReturnsResults(): void
    {
        $results = [];

        $this->k8s()
            ->attach('attach-test')
            ->useStdout()
            ->run(function (string $channel, string $data) use (&$results) {
                if (!empty(trim($data))) {
                    $results[$channel] = $data;
                }
                if (count($results) >= 3) {
                    return false;
                }
            });

        $this->assertArrayHasKey('stdout', $results);
        $this->assertEquals('hihihi', implode('', $results));
    }
}
