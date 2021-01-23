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

namespace K8s\Client\Serialization\Contract;

interface NormalizerInterface
{
    /**
     * @param class-string $modelFqcn
     */
    public function normalize(object $data, string $modelFqcn): array;
}
