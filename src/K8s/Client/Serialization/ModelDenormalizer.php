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

use K8s\Core\Collection;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Model\ApiMachinery\Apis\Meta\v1\WatchEvent;
use ReflectionClass;
use ReflectionObject;

class ModelDenormalizer
{
    public function denormalize(array $data, string $modelFqcn, MetadataCache $cache): object
    {
        $metadata = $cache->get($modelFqcn);

        $instance = (new ReflectionClass($modelFqcn))->newInstanceWithoutConstructor();
        $instanceRef = new ReflectionObject($instance);

        foreach ($metadata->getProperties() as $property) {
            if (!isset($data[$property->getAttributeName()])) {
                continue;
            }
            $phpProperty = $instanceRef->getProperty($property->getName());
            $phpProperty->setAccessible(true);

            $value = $data[$property->getAttributeName()];
            if ($property->isCollection()) {
                $collectionModel = $property->getModelFqcn();

                $value = array_map(function (array $item) use ($collectionModel, $cache) {
                    return $this->denormalize($item, $collectionModel, $cache);
                }, $value);

                $value = new Collection($value);
            } elseif ($property->isModel()) {
                $value = $this->denormalize(
                    $value,
                    $property->getModelFqcn(),
                    $cache
                );
            } elseif ($property->isDateTime()) {
                $value = new \DateTimeImmutable($value);
            // Hacky solution, but we can guess the object type in this case
            } elseif ($modelFqcn === WatchEvent::class && $property->getName() === 'object') {
                $value = $this->denormalize(
                    $value,
                    $cache->getModelFqcnFromKind($value['apiVersion'], $value['kind']),
                    $cache
                );
            }

            $phpProperty->setValue($instance, $value);
        }

        return $instance;
    }
}
