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

namespace K8s\Client;

use K8s\Client\KubeConfig\Model\FullContext;
use K8s\Core\Contract\ContextConfigInterface;
use K8s\Core\Contract\HttpClientFactoryInterface;
use K8s\Core\Contract\WebsocketClientFactoryInterface;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\SimpleCache\CacheInterface;

class Options
{
    public const AUTH_TYPE_BASIC = ContextConfigInterface::AUTH_TYPE_BASIC;

    public const AUTH_TYPE_TOKEN = ContextConfigInterface::AUTH_TYPE_TOKEN;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @var ClientInterface|null
     */
    private $httpClient;

    /**
     * @var WebsocketClientInterface|null
     */
    private $websocketClient;

    /**
     * @var RequestFactoryInterface|null
     */
    private $httpRequestFactory;

    /**
     * @var StreamFactoryInterface|null
     */
    private $streamFactory;

    /**
     * @var UriFactoryInterface|null
     */
    private $uriFactory;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string
     */
    private $authType = self::AUTH_TYPE_TOKEN;

    /**
     * @var FullContext|null
     */
    private $configContext;

    /**
     * @var HttpClientFactoryInterface|null
     */
    private $httpClientFactory;

    /**
     * @var WebsocketClientFactoryInterface|null
     */
    private $websocketClientFactory;

    public function __construct(string $endpoint, string $namespace = 'default')
    {
        $this->endpoint = $endpoint;
        $this->namespace = $namespace;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    public function setHttpClient(ClientInterface $client): self
    {
        $this->httpClient = $client;

        return $this;
    }

    public function getHttpClient(): ?ClientInterface
    {
        return $this->httpClient;
    }

    public function setHttpUriFactory(UriFactoryInterface $uriFactory): self
    {
        $this->uriFactory = $uriFactory;

        return $this;
    }

    public function getHttpUriFactory(): ?UriFactoryInterface
    {
        return $this->uriFactory;
    }

    public function getHttpRequestFactory(): ?RequestFactoryInterface
    {
        return $this->httpRequestFactory;
    }

    public function setHttpRequestFactory(RequestFactoryInterface $httpRequestFactory): self
    {
        $this->httpRequestFactory = $httpRequestFactory;

        return $this;
    }

    public function getStreamFactory(): ?StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getAuthType(): string
    {
        return $this->authType;
    }

    public function setAuthType(string $authType): self
    {
        $this->authType = $authType;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getWebsocketClient(): ?WebsocketClientInterface
    {
        return $this->websocketClient;
    }

    public function setWebsocketClient(WebsocketClientInterface $websocketClient): self
    {
        $this->websocketClient = $websocketClient;

        return $this;
    }

    public function getKubeConfigContext(): ?FullContext
    {
        return $this->configContext;
    }

    public function setKubeConfigContext(FullContext $context): self
    {
        $this->configContext = $context;

        return $this;
    }

    public function getHttpClientFactory(): ?HttpClientFactoryInterface
    {
        return $this->httpClientFactory;
    }

    public function setHttpClientFactory(HttpClientFactoryInterface $httpClientFactory): self
    {
        $this->httpClientFactory = $httpClientFactory;

        return $this;
    }

    public function getWebsocketClientFactory(): ?WebsocketClientFactoryInterface
    {
        return $this->websocketClientFactory;
    }

    public function setWebsocketClientFactory(WebsocketClientFactoryInterface $websocketClientFactory): self
    {
        $this->websocketClientFactory = $websocketClientFactory;

        return $this;
    }
}
