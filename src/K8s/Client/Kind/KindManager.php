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

use K8s\Client\Http\HttpClient;
use K8s\Client\Http\UriBuilder;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Options;
use ReflectionClass;

class KindManager
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var UriBuilder
     */
    private $uriBuilder;

    /**
     * @var MetadataCache
     */
    private $metadataCache;

    /**
     * @var Options
     */
    private $options;

    public function __construct(
        HttpClient $httpClient,
        UriBuilder $uriBuilder,
        MetadataCache $metadataCache,
        Options $options
    ) {
        $this->httpClient = $httpClient;
        $this->uriBuilder = $uriBuilder;
        $this->metadataCache = $metadataCache;
        $this->options = $options;
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
        $options['query'] = $query;

        return $this->execute(
            'post',
            $options,
            $kind
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
        $options['query'] = $query;

        return $this->execute(
            'delete',
            $options,
            $kind,
            ['{name}' => $kind->getName()]
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
    public function deleteNamespaced(string $kindFqcn, $query = [], ?string $namespace = null): object
    {
        $options['query'] = $query;
        $options['namespace'] = $namespace;

        return $this->execute(
            'deletecollection-all',
            $options,
            $kindFqcn
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
        $options['query'] = $query;

        return $this->execute(
            'deletecollection',
            $options,
            $kindFqcn
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
        $options['query'] = $query;

        return $this->execute(
            'get',
            $options,
            $kindFqcn,
            ['{name}' => $name]
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
        $options['query'] = $query;

        return $this->execute(
            'list-all',
            $options,
            $kindFqcn
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
        $options['query'] = $query;
        $options['namespace'] = $namespace;

        return $this->execute(
            'list',
            $options,
            $kindFqcn
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
        $query['watch'] = true;
        $options['query'] = $query;
        $options['handler'] = $handler;

        $this->execute(
            'watch-all',
            $options,
            $kindFqcn
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
        $query['watch'] = true;
        $options['query'] = $query;
        $options['handler'] = $handler;
        $options['namespace'] = $namespace;

        $this->execute(
            'watch',
            $options,
            $kindFqcn
        );
    }

    /**
     * @param class-string|object|null $object
     * @return mixed
     */
    private function execute(
        string $action,
        array $options,
        $object = null,
        array $params = []
    ) {
        $metadata = $this->metadataCache->get(is_object($object) ? get_class($object) : $object);
        $operation = $metadata->getOperationByType($action);

        if ($operation->isBodyRequired()) {
            $options['body'] = $object;
        }
        if ($operation->isResponseSelf()) {
            $options['model'] = $metadata->getModelFqcn();
        } elseif ($operation->getResponseFqcn()) {
            $options['model'] = $operation->getResponseFqcn();
        }
        $query = $options['query'] ?? [];
        $namespace = $options['namespace'] ?? $this->getNamespace($object);
        $uri = $this->uriBuilder->buildUri(
            $operation->getPath(),
            $params,
            $query,
            $namespace
        );

        return $this->httpClient->send(
            $uri,
            $operation->getKubernetesAction(),
            $options
        );
    }

    /**
     * @param array|object|string|null $object
     */
    private function getNamespace($object): string
    {
        if ($object === null || is_string($object)) {
            return $this->options->getNamespace();
        }

        if (is_array($object)) {
            return $object['metadata']['namespace'] ?? $this->options->getNamespace();
        }

        $classRef = new ReflectionClass($object);
        foreach ($classRef->getMethods() as $method) {
            if ($method->getName() === 'getNamespace') {
                $namespace = $method->invoke($object);

                return $namespace ?? $this->options->getNamespace();
            }
        }

        return $this->options->getNamespace();
    }
}
