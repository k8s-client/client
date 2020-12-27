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

namespace Crs\K8s\Http\ResponseHandler;

use Crs\K8s\Http\Contract\ResponseHandlerInterface;
use Crs\K8s\Http\ResponseTrait;
use Crs\K8s\Serialization\Serializer;

abstract class AbstractHandler implements ResponseHandlerInterface
{
    use ResponseTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }
}
