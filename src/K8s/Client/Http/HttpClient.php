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

use K8s\Client\Exception\InvalidArgumentException;
use K8s\Core\Exception\HttpException;
use K8s\Client\Serialization\Serializer;
use K8s\Core\PatchInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

class HttpClient
{
    public const CONTENT_TYPE_JSON = 'application/json';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var ResponseHandlerFactory
     */
    private $handlerFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        RequestFactory $requestFactory,
        ClientInterface $client,
        Serializer $serializer,
        ?ResponseHandlerFactory $handlerFactory = null
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->serializer = $serializer;
        $this->handlerFactory = $handlerFactory ?? new ResponseHandlerFactory();
    }

    /**
     * @param array $options
     * @return mixed
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function send(string $uri, string $action, array $options)
    {
        $model = $options['model'] ?? null;
        $body = $options['body'] ?? null;
        $method = $options['method'] ?? null;

        $encodedBody = null;
        if ($body) {
            $encodedBody = $this->serializer->serialize(
                ($body instanceof PatchInterface) ? $body->toArray() : $body
            );
        }

        $contentType = null;
        if ($body instanceof PatchInterface) {
            $contentType = $body->getContentType();
        }

        $acceptType = null;
        if ($model) {
            $acceptType = RequestFactory::CONTENT_TYPE_JSON;
        }

        try {
            $request = $this->requestFactory->makeRequest(
                $uri,
                $action,
                $acceptType,
                $encodedBody,
                $contentType,
                $method
            );

            $response = $this->client->sendRequest($request);
            $responseHandlers = $this->handlerFactory->makeHandlers($this->serializer);

            foreach ($responseHandlers as $responseHandler) {
                if ($responseHandler->supports($response, $options)) {
                    return $responseHandler->handle($response, $options);
                }
            }

            throw new HttpException(
                sprintf(
                    'There was no supported handler found for the API response to path: %s',
                    $uri
                ),
                $response->getStatusCode()
            );
        } catch (ClientExceptionInterface $exception) {
            throw new HttpException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }
}
