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

use K8s\Api\Service\Core\v1\PodService;
use K8s\Client\Kind\PodLogService;
use unit\K8s\Client\TestCase;

class PodLogServiceTest extends TestCase
{
    /**
     * @var PodService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $podService;

    /**
     * @var PodLogService
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->podService = \Mockery::mock(PodService::class);
        $this->podService->shouldReceive('useNamespace')
            ->with('bar');
        $this->subject = new PodLogService(
            $this->podService,
            'foo',
            'bar'
        );
    }

    public function testItCanReadLogs(): void
    {
        $this->podService->shouldReceive('readNamespacedLog')
            ->with(
                'foo',
                [
                    'previous' => true,
                    'timestamps' => true,
                    'limitBytes' => 100,
                    'pretty' => true,
                    'sinceSeconds' => 1800,
                    'tailLines' => 10,
                    'follow' => false
                ]
            )
            ->andReturn('logs');

        $result = $this->subject
            ->showPrevious()
            ->withTimestamps()
            ->limitBytes(100)
            ->makePretty()
            ->sinceSeconds(1800)
            ->tailLines(10)
            ->read();
        $this->assertEquals('logs', $result);
    }

    public function testItCanFollowLogs(): void
    {
        $callable = function (string $data) {};
        $this->podService->shouldReceive('readNamespacedLog')
            ->with(
                'foo',
                [
                    'timestamps' => true,
                    'pretty' => true,
                    'follow' => true
                ],
                $callable
            )
            ->andReturn('logs');

        $this->subject
            ->withTimestamps()
            ->makePretty()
            ->follow($callable);
    }

    public function testItCanSpecifyPodAndNamespace(): void
    {
        $time = new \DateTime();
        $this->podService->shouldReceive('useNamespace')
            ->with('bar');
        $this->podService->shouldReceive('readNamespacedLog')
            ->with(
                'my-pod',
                [
                    'sinceTime' => $time->format(DATE_ISO8601),
                    'insecureSkipTLSVerifyBackend' => true,
                    'follow' => false,
                ]
            )
            ->andReturn('logs');

        $result = $this->subject
            ->sinceTime($time)
            ->allowInsecure()
            ->usePod('my-pod', 'bar')
            ->read();
        $this->assertEquals('logs', $result);
    }
}
