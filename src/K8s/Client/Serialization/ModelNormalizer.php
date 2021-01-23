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

            if ($property->isModel()) {
                $data[$property->getName()] = $this->normalize(
                    $phpValue,
                    $property->getModelFqcn()
                );
            } elseif ($property->isCollection() && !empty($phpValue)) {
                $data[$property->getName()] = array_map(function (object $item) use ($property) {
                    return $this->normalize(
                        $item,
                        $property->getModelFqcn()
                    );
                }, iterator_to_array($phpValue));
            } elseif ($property->isDateTime() && $phpValue instanceof DateTimeInterface) {
                $data[$property->getName()] = $phpValue->format(DATE_ISO8601);
            } else {
                $data[$property->getName()] = $phpValue;
            }
        }

        return $data;
    }
}
