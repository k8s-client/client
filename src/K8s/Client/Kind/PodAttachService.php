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

use K8s\Api\Service\Core\v1\PodAttachOptionsService;
use K8s\Client\Exception\InvalidArgumentException;
use K8s\Client\Websocket\Contract\ContainerExecInterface;

class PodAttachService
{
    use PodExecTrait;

    /**
     * @var PodAttachOptionsService
     */
    private $service;

    /**
     * @var string
     */
    private $name;

    public function __construct(PodAttachOptionsService $service, string $name, string $namespace)
    {
        $this->service = $service;
        $this->name = $name;
        $this->service->useNamespace($namespace);
    }

    /**
     * Attaches to the  the containers main process with the given handler.
     *
     * @param callable|ContainerExecInterface $handler
     */
    public function run($handler): void
    {
        if (!(is_callable($handler) || $handler instanceof ContainerExecInterface)) {
            throw new InvalidArgumentException(sprintf(
                'The handler for the command must be a callable or ContainerExecInterface instance. Got: %s',
                is_object($handler) ? get_class($handler) : gettype($handler)
            ));
        }

        $this->service->connectGetNamespacedPodAttach(
            $this->name,
            $handler,
            $this->options
        );
    }
}
