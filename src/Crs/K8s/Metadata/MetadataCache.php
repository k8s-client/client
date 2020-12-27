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

namespace Crs\K8s\Metadata;

use Crs\K8s\Annotation\Kind;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\SimpleCache\CacheInterface;

class MetadataCache
{
    /**
     * @var ModelMetadata[]
     */
    private $modelMetadata = [];

    /**
     * @var array<string, string[]>
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

    public function getModelFqcnFromKind(string $apiVersion, string $kind): ?string
    {
        if (empty($this->kindMap)) {
            $this->populateKindMap();
        }

        return $this->kindMap[$apiVersion][$kind] ?? null;
    }

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
        $directory = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__DIR__ . '/../Model')
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
            $fqcn = 'Crs\\K8s' . str_replace(DIRECTORY_SEPARATOR, '\\', $matches[1]);

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
