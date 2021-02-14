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

use K8s\Api\Service\Core\v1\PodPortForwardOptionsService;
use K8s\Client\Websocket\Contract\PortForwardInterface;

class PortForwardService
{
    /**
     * @var string
     */
    private $podName;

    /**
     * @var PodPortForwardOptionsService
     */
    private $portforward;

    /**
     * @var int[]
     */
    private $ports;

    public function __construct(string $podName, array $ports, PodPortForwardOptionsService $portforward)
    {
        $this->portforward = $portforward;
        $this->podName = $podName;
        $this->ports = $ports;
    }

    /**
     * Add a specific port to forward from the pod.
     *
     * @return $this
     */
    public function addPort(int $port): self
    {
        $this->ports[] = $port;

        return $this;
    }

    /**
     * A specific Kubernetes namespace to run the port forward within (where the pod is located).
     *
     * @return $this
     */
    public function useNamespace(string $namespace): self
    {
        $this->portforward->useNamespace($namespace);

        return $this;
    }

    /**
     * Start the port forward process.
     *
     * @param PortForwardInterface|callable $handler
     */
    public function start($handler): void
    {
        $this->portforward->connectGetNamespacedPodPortforward(
            $this->podName,
            $handler,
            ['ports' => $this->ports]
        );
    }
}
