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

use Exception;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\WatchEvent;
use K8s\Client\Exception\RuntimeException;
use K8s\Client\Metadata\ModelPropertyMetadata;
use K8s\Client\Serialization\Contract\DenormalizerInterface;
use K8s\Core\Collection;
use K8s\Client\Metadata\MetadataCache;
use ReflectionClass;
use ReflectionObject;
use UnexpectedValueException;

class ModelDenormalizer implements DenormalizerInterface
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
     * @inheritDoc
     */
    public function denormalize(array $data, ?string $modelFqcn = null): object
    {
        $modelFqcn = $modelFqcn ?? $this->findFModelFqcnFromData($data);
        $metadata = $this->cache->get($modelFqcn);

        $instance = (new ReflectionClass($modelFqcn))->newInstanceWithoutConstructor();
        $instanceRef = new ReflectionObject($instance);

        foreach ($metadata->getProperties() as $property) {
            if (!isset($data[$property->getAttributeName()])) {
                continue;
            }
            $phpProperty = $instanceRef->getProperty($property->getName());
            $phpProperty->setAccessible(true);

            $value = $this->denormalizeValue(
                $modelFqcn,
                $property,
                $data[$property->getAttributeName()]
            );

            $phpProperty->setValue($instance, $value);
        }

        return $instance;
    }

    /**
     * @param class-string $modelFqcn
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    private function denormalizeValue(string $modelFqcn, ModelPropertyMetadata $property, $value)
    {
        if ($property->isCollection()) {
            $collectionModel = $property->getModelFqcn();

            $value = array_map(function (array $item) use ($collectionModel) {
                return $this->denormalize($item, $collectionModel);
            }, $value);

            $value = new Collection($value);
        } elseif ($property->isModel()) {
            $value = $this->denormalize(
                $value,
                $property->getModelFqcn()
            );
        } elseif ($property->isDateTime()) {
            $value = new \DateTimeImmutable($value);
        // Hacky solution, but we can guess the object type in this case
        } elseif ($modelFqcn === WatchEvent::class && $property->getName() === 'object') {
            $value = $this->denormalize(
                $value,
                $this->cache->getModelFqcnFromKind($value['apiVersion'], $value['kind'])
            );
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @return class-string
     */
    private function findFModelFqcnFromData(array $data): string
    {
        $apiVersion = $data['apiVersion'] ?? null;
        $kind = $data['kind'] ?? null;

        if (!(is_string($apiVersion) && $apiVersion !== '')) {
            throw new UnexpectedValueException('The "apiVersion" must be a non-empty string.');
        }
        if (!(is_string($kind) && $kind !== '')) {
            throw new UnexpectedValueException('The "kind" must be a non-empty string.');
        }

        $modelFqcn = $this->cache->getModelFqcnFromKind(
            $apiVersion,
            $kind
        );

        if ($modelFqcn === null) {
            throw new RuntimeException(sprintf(
                'Unable to find a Model for apiVersion "%s" and kind "%s".',
                $apiVersion,
                $kind
            ));
        }

        return $modelFqcn;
    }
}
