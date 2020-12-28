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

namespace K8s\Client\Http\ResponseHandler;

use K8s\Client\Http\Contract\ResponseHandlerInterface;
use K8s\Client\Http\ResponseTrait;
use K8s\Client\Serialization\Serializer;

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
