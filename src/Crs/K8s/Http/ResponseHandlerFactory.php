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

namespace Crs\K8s\Http;

use Crs\K8s\Http\Contract\ResponseHandlerInterface;
use Crs\K8s\Http\ResponseHandler\ErrorHandler;
use Crs\K8s\Http\ResponseHandler\FollowHandler;
use Crs\K8s\Http\ResponseHandler\SuccessHandler;
use Crs\K8s\Http\ResponseHandler\WatchHandler;
use Crs\K8s\Serialization\Serializer;

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
