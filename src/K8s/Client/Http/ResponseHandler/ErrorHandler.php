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

use K8s\Client\Http\Exception\HttpException;
use K8s\Client\Exception\KubernetesException;
use K8s\Client\Http\HttpClient;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;
use Psr\Http\Message\ResponseInterface;

class ErrorHandler extends AbstractHandler
{
    public function handle(ResponseInterface $response, array $options)
    {
        if (!$this->isResponseContentType($response, HttpClient::CONTENT_TYPE_JSON)) {
            throw new HttpException($response);
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
