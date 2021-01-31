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

namespace K8s\Client\Kind;

trait PodExecTrait
{
    /**
     * @var array
     */
    protected $options = [];

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
     * Run in a specific container in the pod.
     */
    public function useContainer(string $container): self
    {
        $this->options['container'] = $container;

        return $this;
    }
}
