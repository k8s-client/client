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

use K8s\Core\Exception\HttpException;
use K8s\Client\Options;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class RequestFactory
{
    public const CONTENT_TYPE_JSON = 'application/json';

    private const ACTION_MAP = [
        'post' => 'POST',
        'get' => 'GET',
        'patch' => 'PATCH',
        'put' => 'PUT',
        'connect' => 'GET',
        'delete' => 'DELETE',
        'deletecollection' => 'DELETE',
        'list' => 'GET',
        'watch' => 'GET',
        'watchlist' => 'GET',
    ];

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var Options
     */
    private $options;

    public function __construct(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        Options $options
    ) {
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->options = $options;
    }

    public function makeRequest(
        string $uri,
        string $action,
        ?string $acceptType = null,
        ?string $body = null
    ): RequestInterface {
        $httpMethod = self::ACTION_MAP[$action] ?? null;

        if ($httpMethod === null) {
            throw new HttpException(sprintf(
                'The action %s has no recognized HTTP method.',
                $action
            ));
        }
        $request = $this->requestFactory->createRequest(
            $httpMethod,
            $uri
        );

        if ($body) {
            $request = $request
                ->withBody($this->streamFactory->createStream($body))
                ->withHeader(
                    'Content-type',
                    self::CONTENT_TYPE_JSON
                );
        }

        if ($acceptType) {
            $request = $request->withHeader(
                'Accept',
                $acceptType
            );
        }

        if ($this->options->getToken()) {
            $request = $request->withHeader(
                'Authorization',
                sprintf('Bearer %s', $this->options->getToken())
            );
        }

        return $request;
    }
}
