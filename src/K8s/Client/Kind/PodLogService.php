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

use K8s\Api\Service\Core\v1\PodService;
use DateTimeInterface;

class PodLogService
{
    /**
     * @var PodService
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

    public function __construct(PodService $service, string $name, string $namespace)
    {
        $this->service = $service;
        $this->name = $name;
        $this->service->useNamespace($namespace);
    }

    /**
     * @param string $name The name of the pod.
     * @param string|null $namespace The namespace for the pod.
     * @return $this
     */
    public function usePod(string $name, ?string $namespace = null): self
    {
        $this->name = $name;
        if ($namespace) {
            $this->service->useNamespace($namespace);
        }

        return $this;
    }

    /**
     * If true, add an RFC3339 or RFC3339Nano timestamp at the beginning of every line of log output. Defaults to false.
     */
    public function withTimestamps(bool $withTimestamps = true): self
    {
        $this->options['timestamps'] = $withTimestamps;

        return $this;
    }

    /**
     * If set, the number of bytes to read from the server before terminating the log output. This may not display a
     * complete final line of logging, and may return slightly more or slightly less than the specified limit.
     */
    public function limitBytes(int $bytes): self
    {
        $this->options['limitBytes'] = $bytes;

        return $this;
    }

    /**
     * Return previous terminated container logs. Defaults to false.
     */
    public function showPrevious(bool $showPrevious = true): self
    {
        $this->options['previous'] = $showPrevious;

        return $this;
    }


    /**
     * The output is pretty printed.
     */
    public function makePretty(): self
    {
        $this->options['pretty'] = true;

        return $this;
    }

    /**
     * A relative time in seconds before the current time from which to show logs. If this value precedes the time a pod
     * was started, only logs since the pod start will be returned. If this value is in the future, no logs will be
     * returned.
     */
    public function sinceSeconds(int $seconds): self
    {
        $this->options['sinceSeconds'] = $seconds;

        return $this;
    }

    /**
     * Retrieve the logs since the specified time.
     *
     * Note: This option is referenced, but not documented, in the official API docs for some reason.
     */
    public function sinceTime(DateTimeInterface $time): self
    {
        $this->options['sinceTime'] = $time->format(DATE_ISO8601);

        return $this;
    }

    /**
     * Indicates that the apiserver should not confirm the validity of the serving certificate of the backend it is
     * connecting to.
     */
    public function allowInsecure(): self
    {
        $this->options['insecureSkipTLSVerifyBackend'] = true;

        return $this;
    }

    /**
     * If set, the number of lines from the end of the logs to show. If not specified, logs are shown from the creation
     * of the container or sinceSeconds
     */
    public function tailLines(int $lines): self
    {
        $this->options['tailLines'] = $lines;

        return $this;
    }

    /**
     * Follow the log stream of the pod.
     *
     * @param callable $handler The callable to use for received data. Return false from the callable to stop following.
     * @param string|null $container A specific container to use for logs. Defaults to the only container if there is one.
     */
    public function follow(callable $handler, ?string $container = null): void
    {
        $options = $this->options;
        $options['follow'] = true;
        if ($container) {
            $options['container'] = $container;
        }

        $this->service->readNamespacedLog(
            $this->name,
            $options,
            $handler
        );
    }

    /**
     * Read and return the logs from a container with the options specified.
     *
     * @param string|null $container A specific container to use for logs. Defaults to the only container if there is one.
     * @return string
     */
    public function read(?string $container = null): string
    {
        $options = $this->options;
        $options['follow'] = false;
        if ($container) {
            $options['container'] = $container;
        }

        return (string)$this->service->readNamespacedLog(
            $this->name,
            $options
        );
    }
}
