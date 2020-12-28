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

use K8s\Core\Exception\Exception;
use K8s\Client\Metadata\MetadataCache;

class Serializer
{
    /**
     * @var MetadataCache
     */
    private $metadataCache;

    /**
     * @var ModelNormalizer|null
     */
    private $normalizer;

    /**
     * @var ModelDenormalizer|null
     */
    private $denormalizer;

    public function __construct(
        ?MetadataCache $metadataCache = null,
        ?ModelNormalizer $normalizer = null,
        ?ModelDenormalizer $denormalizer = null
    ) {
        $this->metadataCache = $metadataCache ?? new MetadataCache();
        $this->normalizer = $normalizer ?? new ModelNormalizer();
        $this->denormalizer = $denormalizer ?? new ModelDenormalizer();
    }

    /**
     * @param object|array $data
     */
    public function serialize($data): string
    {
        if (!is_array($data)) {
            $data = $this->normalizer->normalize(
                $data,
                get_class($data),
                $this->metadataCache
            );
        }

        return json_encode($data);
    }

    /**
     * @param string|array $data
     */
    public function deserialize($data, string $model): object
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!is_array($data)) {
            throw new Exception(sprintf('Unable to deserialize data of type %s', gettype($data)));
        }

        return $this->denormalizer->denormalize(
            $data,
            $model,
            $this->metadataCache
        );
    }
}
