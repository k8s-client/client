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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class MetadataCache
{
    private const MODEL_PATHS = [
        __DIR__ . '/../../../../../api/src/K8s/Api/Model',
        __DIR__ . '/../../../../vendor/k8s/api/src/K8s/Api/Model',
    ];

    private const PREFIX_KIND_MAP = 'kind-map';

    private const PREFIX_KIND_META = 'kind-meta';

    /**
     * @var array<class-string, ModelMetadata>
     */
    private $modelMetadata = [];

    /**
     * @var array<string, class-string[]>|null
     */
    private $kindMap = null;

    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @var MetadataParser
     */
    private $parser;

    public function __construct(
        ?CacheInterface $cache = null,
        ?MetadataParser $parser = null
    ) {
        $this->parser = $parser ?? new MetadataParser();
        $this->cache = $cache;
    }

    /**
     * Cache all kind map data and Kind model metadata.
     *
     * @return void
     */
    public function cacheAll(): void
    {
        if (!$this->cache) {
            return;
        }
        $this->initKindMap();

        foreach ($this->kindMap as $kinds) {
            foreach ($kinds as $modelFqcn) {
                $this->getOrCacheMetadata($modelFqcn);
            }
        }
    }

    /**
     * Delete all cache for kind map data and Kind model metadata.
     *
     * @return void
     */
    public function deleteAll(): void
    {
        if (!$this->cache) {
            return;
        }
        $this->initKindMap(false);
        $this->cache->delete(self::PREFIX_KIND_MAP);

        $modelClasses = [];
        foreach ($this->kindMap as $kinds) {
            foreach ($kinds as $modelFqcn) {
               $modelClasses[] = self::PREFIX_KIND_META . $modelFqcn;
            }
        }

        $this->cache->deleteMultiple($modelClasses);
    }

    /**
     * @param class-string $modelFqcn
     */
    public function get(string $modelFqcn): ModelMetadata
    {
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
        $this->initKindMap();

        return $this->kindMap[$apiVersion][$kind] ?? null;
    }

    private function initKindMap(bool $cache = true): void
    {
        if ($this->kindMap !== null) {
            return;
        }

        $kindMap = $this->cache
            ? $this->cache->get(self::PREFIX_KIND_MAP)
            : null;

        if (!$kindMap) {
            $this->generateKindMap();
        }

        if ($this->cache && $cache) {
            $this->cache->set(
                self::PREFIX_KIND_MAP,
                $this->kindMap
            );
        }
    }

    /**
     * @param class-string $modelFqcn
     */
    private function getOrCacheMetadata(string $modelFqcn): ModelMetadata
    {
        $metadata = $this->cache->get(self::PREFIX_KIND_META . $modelFqcn);
        if (!$metadata) {
            $metadata = $this->parser->parse($modelFqcn);
            $this->cache->set(
                self::PREFIX_KIND_META . $modelFqcn,
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

    private function generateKindMap(): void
    {
        if ($this->kindMap !== null) {
            return;
        }
        $this->kindMap = [];
        $path = null;

        foreach (self::MODEL_PATHS as $modelPath) {
            $modelPath = str_replace('/', DIRECTORY_SEPARATOR, $modelPath);
            if (file_exists($modelPath)) {
                $path = $modelPath;
                break;
            }
        }

        if ($path === null) {
            throw new RuntimeException(sprintf(
                'Unable to locate the path to the Kubernetes API model classes. Paths tried: %s',
                implode(', ', self::MODEL_PATHS)
            ));
        }

        $directory = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        $reader = new AnnotationReader();
        foreach ($directory as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (substr($file->getPathname(), -4) !== '.php') {
                continue;
            }
            $ds = '\\' . DIRECTORY_SEPARATOR;
            preg_match("/(" . $ds . "Model" . $ds . ".*).php$/", $file->getPathname(), $matches);
            if (!isset($matches[1])) {
                continue;
            }
            /** @var class-string $fqcn */
            $fqcn = 'K8s\\Api';
            if (DIRECTORY_SEPARATOR === '/') {
                $fqcn .= str_replace(DIRECTORY_SEPARATOR, '\\', $matches[1]);
            } else {
                $fqcn .= $matches[1];
            }

            $class = new ReflectionClass($fqcn);
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
