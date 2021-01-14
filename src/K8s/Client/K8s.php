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

namespace K8s\Client;

use K8s\Api\Service\ServiceFactory;
use K8s\Client\Kind\PodExecService;
use K8s\Client\Kind\PodLogService;

class K8s
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Options
     */
    private $options;

    public function __construct(Options $options)
    {
        $this->factory = new Factory($options);
        $this->options = $options;
    }

    /**
     * Helps retrieve the API service for a specific Kind at a specific version. The service contains all possible
     * operations for that Kind and version. This provides a slightly different way of interacting with the API than
     * through the base abstracted methods on this client.
     *
     * @return ServiceFactory
     */
    public function api(): ServiceFactory
    {
        return $this->factory->makeServiceFactory();
    }

    /**
     * Create a Kubernetes resource.
     *
     * @param object $kind Any Kind model object.
     * @param array $query Any additional query parameters.
     * @return object
     */
    public function create(object $kind, $query = []): object
    {
        return $this->factory->makeKindManager()->create($kind, $query);
    }

    /**
     * Delete a Kubernetes resource.
     *
     * @param object $kind Any Kind model object.
     * @param array $query Any additional query parameters.
     * @return object
     */
    public function delete(object $kind, $query = []): object
    {
        return $this->factory->makeKindManager()->delete($kind, $query);
    }

    /**
     * Read a Kubernetes resource of a specific Kind.
     *
     * @param string $name the name of the resource.
     * @param class-string $kindFqcn The fully-qualified class name of the resource to read.
     * @param array $query Any additional query parameters.
     * @return object
     */
    public function read(string $name, string $kindFqcn, $query = []): object
    {
        return $this->factory->makeKindManager()->read($name, $kindFqcn, $query);
    }

    /**
     * Delete all Kubernetes resource of a specific kind.
     *
     * @param class-string $kindFqcn The fully-qualified class name of the resource to delete.
     * @param array $query Any additional query parameters.
     * @return object Typically the Status object on success.
     */
    public function deleteAll(string $kindFqcn, $query = []): object
    {
        return $this->factory->makeKindManager()->deleteAll(
            $kindFqcn,
            $query
        );
    }

    /**
     * Delete all Kubernetes resource of a specific kind in a namespace.
     *
     * @param class-string $kindFqcn The fully-qualified class name of the resource to delete.
     * @param array $query Any additional query parameters.
     * @param string|null $namespace The namespace. If not supplied, it will use the default namespace from the options.
     * @return object Typically the Status object on success.
     */
    public function deleteAllNamespaced(string $kindFqcn, $query = [], ?string $namespace = null): object
    {
        return $this->factory->makeKindManager()->deleteAllNamespaced(
            $kindFqcn,
            $query,
            $namespace
        );
    }

    /**
     * Watch all Kubernetes resources of a specific Kind with a callable.
     *
     * @param callable $handler The callable to invoke for each watched resource.
     * @param class-string $kindFqcn The fully-qualified class name of the resource to list.
     * @param array $query Any additional query parameters.
     */
    public function watchAll(callable $handler, string $kindFqcn, $query = []): void
    {
        $this->factory->makeKindManager()->watchAll(
            $handler,
            $kindFqcn,
            $query
        );
    }

    /**
     * Watch a Kubernetes resource of a specific Kind with a callable in a namespace.
     *
     * @param callable $handler The callable to invoke for each watched resource.
     * @param class-string $kindFqcn The fully-qualified class name of the resource to list.
     * @param array $query Any additional query parameters.
     * @param string|null $namespace The namespace. If not supplied, it will use the default namespace from the options.
     */
    public function watchNamespaced(callable $handler, string $kindFqcn, $query = [], ?string $namespace = null): void
    {
        $this->factory->makeKindManager()->watchNamespaced(
            $handler,
            $kindFqcn,
            $query,
            $namespace
        );
    }

    /**
     * List all Kubernetes resource of a specific kind.
     *
     * @param class-string $kindFqcn The fully-qualified class name of the resource to list.
     * @param array $query Any additional query parameters.
     * @return iterable<int, object>
     */
    public function listAll(string $kindFqcn, $query = []): iterable
    {
        return $this->factory->makeKindManager()->listAll(
            $kindFqcn,
            $query
        );
    }

    /**
     * List all Kubernetes resource of a specific kind in a namespace.
     *
     * @param class-string $kindFqcn The fully-qualified class name of the resource to list.
     * @param array $query Any additional query parameters.
     * @param string|null $namespace The namespace. If not supplied, it will use the default namespace from the options.
     * @return iterable<int, object>
     */
    public function listNamespaced(string $kindFqcn, $query = [], ?string $namespace = null): iterable
    {
        return $this->factory->makeKindManager()->listNamespaced(
            $kindFqcn,
            $query,
            $namespace
        );
    }

    /**
     * Query / follow logs for a pod.
     *
     * @param string $podName The pod name.
     * @param string|null $namespace An optional namespace (Otherwise the default is used).
     */
    public function logs(string $podName, ?string $namespace = null): PodLogService
    {
        return new PodLogService(
            $this->api()->v1CorePod(),
            $podName,
            $namespace ?? $this->options->getNamespace()
        );
    }

    /**
     * Execute commands within pod.
     *
     * @param string $podName The pod name.
     * @param string|string[] $command The command to run.
     * @param string|null $namespace An optional namespace (Otherwise the default is used).
     */
    public function exec(string $podName, $command = [], ?string $namespace = null): PodExecService
    {
        $exec = new PodExecService(
            $this->api()->v1CorePodExecOptions(),
            $podName,
            $namespace ?? $this->options->getNamespace()
        );

        if (!empty($command)) {
            $exec->command($command);
        }

        return $exec;
    }
}
