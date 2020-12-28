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

use K8s\Client\Http\HttpClient;
use Psr\Http\Message\ResponseInterface;

class SuccessHandler extends AbstractHandler
{
    public function handle(ResponseInterface $response, array $options)
    {
        $model = $options['model'] ?? null;

        $result = (string)$response->getBody();
        if ($result && $model && $this->isResponseContentType($response, HttpClient::CONTENT_TYPE_JSON)) {
            return $this->serializer->deserialize($result, $model);
        }

        return $result;
    }

    public function supports(ResponseInterface $response, array $options): bool
    {
        return $this->isResponseSuccess($response);
    }
}
