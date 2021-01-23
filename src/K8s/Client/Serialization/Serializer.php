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

use K8s\Client\Serialization\Contract\DenormalizerInterface;
use K8s\Client\Serialization\Contract\NormalizerInterface;
use K8s\Core\Exception\Exception;

class Serializer
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param object|array $data
     */
    public function serialize($data): string
    {
        if (!is_array($data)) {
            $data = $this->normalizer->normalize(
                $data,
                get_class($data)
            );
        }

        return (string)json_encode($data);
    }

    /**
     * @param string|array $data
     * @param class-string $model
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
            $model
        );
    }
}
