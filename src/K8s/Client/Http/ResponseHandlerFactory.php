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

namespace K8s\Client\Http;

use K8s\Client\Http\Contract\ResponseHandlerInterface;
use K8s\Client\Http\ResponseHandler\ErrorHandler;
use K8s\Client\Http\ResponseHandler\FollowHandler;
use K8s\Client\Http\ResponseHandler\SuccessHandler;
use K8s\Client\Http\ResponseHandler\WatchHandler;
use K8s\Client\Serialization\Serializer;

class ResponseHandlerFactory
{
    /**
     * @return ResponseHandlerInterface[]
     */
    public function makeHandlers(Serializer $serializer): array
    {
        return [
            new ErrorHandler($serializer),
            new FollowHandler($serializer),
            new WatchHandler($serializer),
            new SuccessHandler($serializer),
        ];
    }
}
