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

use Crs\K8s\Http\Exception\HttpException;
use Crs\K8s\Exception\KubernetesException;
use Crs\K8s\Http\HttpClient;
use Crs\K8s\Model\ApiMachinery\Apis\Meta\v1\Status;
use Psr\Http\Message\ResponseInterface;

class ErrorHandler extends AbstractHandler
{
    public function handle(ResponseInterface $response, array $options)
    {
        if (!$this->isResponseContentType($response, HttpClient::CONTENT_TYPE_JSON)) {
            throw new HttpException(
                $response->getReasonPhrase(),
                $response->getStatusCode()
            );
        }

        /** @var Status $status */
        $status = $this->serializer->deserialize(
            (string)$response->getBody(),
            Status::class
        );

        throw new KubernetesException($status);
    }

    public function supports(ResponseInterface $response, array $options): bool
    {
        return $this->isResponseError($response);
    }
}
