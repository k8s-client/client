<?php

/**
 * This file is part of the crs/k8s library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crs\K8s\Kind;

use Crs\K8s\Exception\InvalidArgumentException;
use Crs\K8s\Service\Core\v1\PodExecOptionsService;
use Crs\K8s\Websocket\Contract\ContainerExecInterface;

class PodExecService
{
    /**
     * @var PodExecOptionsService
     */
    private $service;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options = [];

    public function __construct(PodExecOptionsService $service, string $name, string $namespace)
    {
        $this->service = $service;
        $this->name = $name;
        $this->service->useNamespace($namespace);
    }

    /**
     * TTY if true indicates that a tty will be allocated for the exec call. Defaults to false.
     */
    public function useTty(bool $useTty = true): self
    {
        $this->options['tty'] = $useTty;

        return $this;
    }

    /**
     * Redirect the standard output stream of the pod for this call. Defaults to true.
     */
    public function useStdout(bool $useStdout = true): self
    {
        $this->options['stdout'] = $useStdout;

        return $this;
    }

    /**
     * Redirect the standard error stream of the pod for this call. Defaults to true.
     */
    public function useStderr(bool $useStderr = true): self
    {
        $this->options['stderr'] = $useStderr;

        return $this;
    }

    /**
     * Redirect the standard output stream of the pod for this call. Defaults to true.
     */
    public function useStdin(bool $useStdin = true): self
    {
        $this->options['stdin'] = $useStdin;

        return $this;
    }

    /**
     * Command is the remote command to execute. argv array. Not executed within a shell.
     *
     * @param string|string[] $command
     */
    public function command($command): self
    {
        /** @todo array of strings not being handled correctly when building up the query string... */
        $this->options['command'] = $command;

        return $this;
    }

    /**
     * Executes the command with the given handler in the Pod.
     *
     * @param callable|ContainerExecInterface $handler
     */
    public function run($handler, ?string $container = null): void
    {
        $options = $this->options;
        if ($container) {
            $options['container'] = $container;
        }

        if (!(is_callable($handler) || $handler instanceof ContainerExecInterface)) {
            throw new InvalidArgumentException(sprintf(
                'The handler for the command must be a callable or ContainerExecInterface instance. Got: %s',
                is_object($handler) ? get_class($handler) : gettype($handler)
            ));
        }

        $this->service->connectGetNamespacedPodExec(
            $this->name,
            $handler,
            $options
        );
    }
}
