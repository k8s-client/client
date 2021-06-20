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
use K8s\Client\File\FileDownloader;
use K8s\Client\File\FileUploader;
use K8s\Client\Kind\PodAttachService;
use K8s\Client\Kind\PodExecService;
use K8s\Client\Kind\PodLogService;
use K8s\Client\Kind\PortForwardService;
use K8s\Core\PatchInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @param string|null $namespace The namespace to create it in (uses default from options if not defined).
     * @return object
     */
    public function create(object $kind, $query = [], ?string $namespace = null): object
    {
        return $this->factory->makeKindManager()->create(
            $kind,
            $query,
            $namespace
        );
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
        return $this->factory->makeKindManager()->delete(
            $kind,
            $query
        );
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
        return $this->factory->makeKindManager()->read(
            $name,
            $kindFqcn,
            $query
        );
    }

    /**
     * Read a Kubernetes status sub-resource of a specific Kind.
     *
     * @param string $name the name of the Kind.
     * @param class-string $kindFqcn The fully-qualified class name of the resource to read.
     * @param array $query Any additional query parameters.
     * @return object
     */
    public function readStatus(string $name, string $kindFqcn, $query = []): object
    {
        return $this->factory->makeKindManager()->readStatus(
            $name,
            $kindFqcn,
            $query
        );
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
     * Patch a Kubernetes resource using a patch object (json, strategic, merge).
     *
     * @param object $kind Any Kind model object.
     * @param PatchInterface $patch A patch class object.
     * @param array $query Any additional query parameters.
     * @param string|null $namespace The namespace the Kind resides in (uses default from options if not defined).
     * @return object This would typically be the same object passed in as the Kind.
     */
    public function patch(object $kind, PatchInterface $patch, array $query = [], ?string $namespace = null): object
    {
        return $this->factory->makeKindManager()->patch(
            $kind,
            $patch,
            $query,
            $namespace
        );
    }

    /**
     * Patch a Kubernetes status sub-resource using a patch object (json, strategic, merge).
     *
     * @param object $kind Any Kind model object.
     * @param PatchInterface $patch A patch class object.
     * @param array $query Any additional query parameters.
     * @param string|null $namespace The namespace the Kind resides in (uses default from options if not defined).
     * @return object This would typically be the same object passed in as the Kind.
     */
    public function patchStatus(object $kind, PatchInterface $patch, array $query = [], ?string $namespace = null): object
    {
        return $this->factory->makeKindManager()->patchStatus(
            $kind,
            $patch,
            $query,
            $namespace
        );
    }

    /**
     * Replace a Kubernetes resource (an atomic patch operation that requires a resourceVersion).
     *
     * @param object $kind The Kind object model.
     * @param array $query Any additional query parameters.
     * @return object The Kind model object being replaced.
     */
    public function replace(object $kind, array $query = []): object
    {
        return $this->factory->makeKindManager()->replace(
            $kind,
            $query
        );
    }

    /**
     * Replace a Kubernetes status sub-resource of the specified Kind (an atomic patch operation that requires a resourceVersion).
     *
     * @param object $kind The Kind object model.
     * @param array $query Any additional query parameters.
     * @return object The Kind model object being replaced.
     */
    public function replaceStatus(object $kind, array $query = []): object
    {
        return $this->factory->makeKindManager()->replaceStatus(
            $kind,
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
     * Proxy an HTTP request to a Pod, Service, or Node Kind object. The raw response object is returned.
     *
     * @param object $kind The Kind object model.
     * @param RequestInterface $request The request to proxy to the Kind.
     */
    public function proxy(object $kind, RequestInterface $request): ResponseInterface
    {
        return $this->factory->makeKindManager()->proxy(
            $kind,
            $request
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
     * Attach to the main running process of a container in a Pod.
     *
     * @param string $podName The Pod name to attach to.
     * @param string|null $container The specific container to attach to. If not specified, defaults to the first container.
     * @param string|null $namespace The specific namespace the pod is in. Defaults to the one specified in options.
     * @return PodAttachService
     */
    public function attach(string $podName, ?string $container = null, ?string $namespace = null): PodAttachService
    {
        $attach = new PodAttachService(
            $this->api()->v1CorePodAttachOptions(),
            $podName,
            $namespace ?? $this->options->getNamespace()
        );

        if (!empty($container)) {
            $attach->useContainer($container);
        }

        return $attach;
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

    /**
     * Upload files to a Pod.
     *
     * @param string $podName The pod name.
     * @param string|null $source Optionally specify a file to upload.
     * @param string|null $destination The destination for the file to upload.
     * @return FileUploader
     * @throws File\Exception\FileUploadException
     */
    public function uploader(string $podName, ?string $source = null, ?string $destination = null) : FileUploader
    {
        $fileUpload = new FileUploader(
            $this->factory->makeArchiveFactory(),
            $this->exec($podName)
        );

        if ($source !== null && $destination !== null) {
            $fileUpload->addFile($source, $destination);
        }

        return $fileUpload;
    }

    /**
     * Download files from a Pod.
     *
     * @param string $podName The pod name.
     * @param string|string[] $path The path(s) to download from. Either a string path, or an array of paths.
     * @return FileDownloader
     */
    public function downloader(string $podName, $path = []): FileDownloader
    {
        $fileDownloader = new FileDownloader(
            $this->exec($podName),
            $this->factory->makeArchiveFactory()
        );

        if (!empty($path)) {
            $fileDownloader->from($path);
        }

        return $fileDownloader;
    }

    /**
     * Forward port(s) from a pod to communicate with the port(s) locally.
     *
     * @param string $podName The pod name to port-forward from.
     * @param int|array $port The port, or array of ports, to forward.
     * @return PortForwardService
     */
    public function portforward(string $podName, $port): PortForwardService
    {
        return new PortForwardService(
            $podName,
            array_map('intval', (array)$port),
            $this->api()->v1CorePodPortForwardOptions()
        );
    }

    /**
     * @param array<string, mixed> $data The Kind data as an array.
     * @return object The Model object represented by the data.
     */
    public function newKind(array $data): object
    {
        return $this->factory->makeDenormalizer()->denormalize($data);
    }

    /**
     * Get the options the client was constructed with.
     *
     * @return Options
     */
    public function getOptions(): Options
    {
        return $this->options;
    }
}
