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

namespace unit\K8s\Client\KubeConfig\Model;

use K8s\Client\KubeConfig\Model\Cluster;
use unit\K8s\Client\TestCase;

class ClusterTest extends TestCase
{
    /**
     * @var Cluster
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Cluster([
            'name' => 'foo',
            'cluster' => [
                'certificate-authority' => 'meh',
                'certificate-authority-data' => 'meh-data',
                'server' => 'https://foo',
            ]
        ]);
    }

    public function testGetServer(): void
    {
        $this->assertEquals('https://foo', $this->subject->getServer());
    }

    public function testGetName(): void
    {
        $this->assertEquals('foo', $this->subject->getName());
    }

    public function testGetCertificateAuthority(): void
    {
        $this->assertEquals('meh', $this->subject->getCertificateAuthority());
    }
}
