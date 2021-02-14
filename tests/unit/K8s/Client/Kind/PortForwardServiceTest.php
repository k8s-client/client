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

namespace unit\K8s\Client\Kind;

use K8s\Api\Service\Core\v1\PodPortForwardOptionsService;
use K8s\Client\Kind\PortForwardService;
use unit\K8s\Client\TestCase;

class PortForwardServiceTest extends TestCase
{
    /**
     * @var PodPortForwardOptionsService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $service;

    /**
     * @var PortForwardService
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = \Mockery::spy(PodPortForwardOptionsService::class);
        $this->subject = new PortForwardService(
            'foo',
            [80],
            $this->service
        );
    }

    public function testItAddsPorts(): void
    {
        $handler = function () {};

        $this->subject->addPort(443);
        $this->subject->start($handler);

        $this->service->shouldHaveReceived(
            'connectGetNamespacedPodPortforward',
            ['foo', $handler, ['ports' => [80, 443]]]
        );
    }

    public function testItSwitchesNamespaces(): void
    {
        $this->subject->useNamespace('bar');

        $this->service->shouldHaveReceived('useNamespace', ['bar']);
    }

    public function testItStartsWithTheCorrectParameters(): void
    {
        $handler = function () {};
        $this->subject->start($handler);

        $this->service->shouldHaveReceived(
            'connectGetNamespacedPodPortforward',
            ['foo', $handler, ['ports' => [80]]]
        );
    }
}
