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

use K8s\Api\Service\Core\v1\PodAttachOptionsService;
use K8s\Client\Exception\InvalidArgumentException;
use K8s\Client\Kind\PodAttachService;
use unit\K8s\Client\TestCase;

class PodAttachServiceTest extends TestCase
{
    /**
     * @var PodAttachOptionsService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $service;

    /**
     * @var PodAttachService
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = \Mockery::spy(PodAttachOptionsService::class);
        $this->subject = new PodAttachService(
            $this->service,
            'foo',
            'bar'
        );
    }

    public function testAttachWithWrongTypeOfHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->subject->run(new \stdClass());
    }

    public function testItCallsTheService(): void
    {
        $handler = function () {};
        $this->subject->run($handler);

        $this->service->shouldHaveReceived('connectGetNamespacedPodAttach', ['foo', $handler, []]);
    }
}
