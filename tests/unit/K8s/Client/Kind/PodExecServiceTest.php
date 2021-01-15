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

use K8s\Api\Service\Core\v1\PodExecOptionsService;
use K8s\Client\Kind\PodExecService;
use K8s\Client\Websocket\Contract\ContainerExecInterface;
use unit\K8s\Client\TestCase;

class PodExecServiceTest extends TestCase
{
    /**
     * @var PodExecOptionsService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $podExecOptions;

    /**
     * @var ContainerExecInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $containerExec;

    /**
     * @var PodExecService
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->podExecOptions = \Mockery::mock(PodExecOptionsService::class);
        $this->containerExec = \Mockery::spy(ContainerExecInterface::class);

        $this->podExecOptions->shouldReceive('useNamespace')
            ->with('default');

        $this->subject = new PodExecService(
            $this->podExecOptions,
            'foo',
            'default'
        );
    }

    public function testItRunsCommandsWithStdInOutAndErr(): void
    {
        $this->podExecOptions->shouldReceive('connectGetNamespacedPodExec')
            ->with(
                'foo',
                $this->containerExec,
                [
                    'stdin' => true,
                    'stdout' => true,
                    'stderr' => true,
                    'command' => '/usr/bin/whoami'
                ]
            );

        $this->subject
            ->useStdin()
            ->useStdout()
            ->useStderr()
            ->command('/usr/bin/whoami')
            ->run($this->containerExec);
    }

    public function testItRunsCommandsWithStdInOutAndTty(): void
    {
        $this->podExecOptions->shouldReceive('connectGetNamespacedPodExec')
            ->with(
                'foo',
                $this->containerExec,
                [
                    'stdin' => true,
                    'stdout' => true,
                    'tty' => true,
                    'command' => ['/usr/bin/whoami', '--version']
                ]
            );

        $this->subject
            ->useStdin()
            ->useStdout()
            ->useTty()
            ->command(['/usr/bin/whoami', '--version'])
            ->run($this->containerExec);
    }

    public function testItRunsCommandsWithCallable(): void
    {
        $callable = function (string $channel, string $data) {};
        $this->podExecOptions->shouldReceive('connectGetNamespacedPodExec')
            ->with(
                'foo',
                $callable,
                [
                    'stdin' => true,
                    'stdout' => true,
                    'tty' => true,
                    'command' => ['/usr/bin/whoami', '--version']
                ]
            );

        $this->subject
            ->useStdin()
            ->useStdout()
            ->useTty()
            ->command(['/usr/bin/whoami', '--version'])
            ->run($callable);
    }
}
