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

namespace K8s\Client\Serialization;

use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Metadata\ModelPropertyMetadata;
use K8s\Client\Serialization\Contract\NormalizerInterface;
use K8s\Core\Collection;
use DateTimeInterface;

class ModelNormalizer implements NormalizerInterface
{
    /**
     * @var MetadataCache
     */
    private $cache;

    public function __construct(MetadataCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param object $model
     * @param class-string $modelFqcn
     */
    public function normalize($model, string $modelFqcn): array
    {
        $data = [];
        $metadata = $this->cache->get($modelFqcn);

        $instanceRef = new \ReflectionObject($model);
        foreach ($metadata->getProperties() as $property) {
            $phpProperty = $instanceRef->getProperty($property->getName());
            $phpProperty->setAccessible(true);
            $phpValue = $phpProperty->getValue($model);
            if ($phpValue === null) {
                continue;
            }
            if ($phpValue instanceof Collection && $phpValue->isEmpty()) {
                continue;
            }
            $data[$property->getName()] = $this->normalizeValue(
                $property,
                $phpValue
            );
        }

        return $data;
    }

    /**
     * @param ModelPropertyMetadata $property
     * @param mixed $value
     * @return mixed
     */
    private function normalizeValue(ModelPropertyMetadata $property, $value)
    {
        if ($property->isModel()) {
            return (object)$this->normalize(
                $value,
                $property->getModelFqcn()
            );
        } elseif ($property->isCollection() && !empty($value)) {
            return array_map(function (object $item) use ($property) {
                return (object)$this->normalize(
                    $item,
                    $property->getModelFqcn()
                );
            }, iterator_to_array($value));
        } elseif ($property->isDateTime() && $value instanceof DateTimeInterface) {
            return $value->format(DATE_RFC3339);
        }

        return $value;
    }
}
