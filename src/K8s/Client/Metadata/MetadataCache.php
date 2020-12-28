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

namespace K8s\Client\Metadata;

use K8s\Client\Exception\RuntimeException;
use K8s\Core\Annotation\Kind;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\SimpleCache\CacheInterface;

class MetadataCache
{
    private const MODEL_PATHS = [
        __DIR__ . '/../../../../../api/src/K8s/Api/Model',
        __DIR__ . '/../../../../vendor/k8s/api/src/K8s/Api/Model',
    ];

    /**
     * @var array<class-string, ModelMetadata>
     */
    private $modelMetadata = [];

    /**
     * @var array<string, class-string[]>
     */
    private $kindMap = [];

    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @var MetadataParser
     */
    private $parser;

    public function __construct(
        ?MetadataParser $parser = null,
        ?CacheInterface $cache = null
    ) {
        $this->parser = $parser ?? new MetadataParser();
        $this->cache = $cache;
    }

    /**
     * @param class-string $modelFqcn
     */
    public function get(string $modelFqcn): ModelMetadata
    {
        $metadata = null;
        if ($this->cache) {
            $metadata = $this->getOrCacheMetadata($modelFqcn);
        } else {
            $metadata = $this->getMetadataWithoutCache($modelFqcn);
        }

        return $metadata;
    }

    /**
     * @return class-string|null
     */
    public function getModelFqcnFromKind(string $apiVersion, string $kind): ?string
    {
        if (empty($this->kindMap)) {
            $this->populateKindMap();
        }

        return $this->kindMap[$apiVersion][$kind] ?? null;
    }

    /**
     * @param class-string $modelFqcn
     */
    private function getOrCacheMetadata(string $modelFqcn): ModelMetadata
    {
        $metadata = $this->cache->get($modelFqcn);
        if (!$metadata) {
            $metadata = $this->parser->parse($modelFqcn);
            $this->cache->set(
                $modelFqcn,
                $metadata
            );
        }

        return $metadata;
    }

    /**
     * @param class-string $modelFqcn
     */
    private function getMetadataWithoutCache(string $modelFqcn): ModelMetadata
    {
        if (isset($this->modelMetadata[$modelFqcn])) {
            return $this->modelMetadata[$modelFqcn];
        }
        $this->modelMetadata[$modelFqcn] = $this->parser->parse($modelFqcn);

        return $this->modelMetadata[$modelFqcn];
    }

    private function populateKindMap(): void
    {
        $path = null;

        foreach (self::MODEL_PATHS as $modelPath) {
            if (file_exists($modelPath)) {
                $path = $modelPath;
                break;
            }
        }

        if ($path === null) {
            throw new RuntimeException('Unable to locate the path to the Kubernetes API model classes.');
        }

        $directory = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        $reader = new AnnotationReader();
        foreach ($directory as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (substr($file->getPathname(), -4) !== '.php') {
                continue;
            }
            preg_match('/(\/Model\/.*).php$/', $file->getPathname(), $matches);
            if (!isset($matches[1])) {
                continue;
            }
            /** @var class-string $fqcn */
            $fqcn = 'K8s\\Api' . str_replace(DIRECTORY_SEPARATOR, '\\', $matches[1]);

            $class = new \ReflectionClass($fqcn);
            $classKind = $reader->getClassAnnotation($class, Kind::class);
            if (!$classKind) {
                continue;
            }
            /** @var Kind $classKind */
            $apiVersion = $classKind->group;

            if ($apiVersion) {
                $apiVersion .= '/';
            }
            $apiVersion .= $classKind->version;

            if (!isset($this->kindMap[$apiVersion])) {
                $this->kindMap[$apiVersion] = [];
            }

            $this->kindMap[$apiVersion][$classKind->kind] = $fqcn;
        }
    }
}
