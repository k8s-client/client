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

namespace unit\K8s\Client\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use K8s\Client\Exception\RuntimeException;
use K8s\Client\Http\RequestFactory;
use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Client\Options;
use unit\K8s\Client\TestCase;

class RequestFactoryTest extends TestCase
{
    /**
     * @var RequestFactory
     */
    private $subject;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var ContextConfigFactory
     */
    private $configFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->options = new Options('https://foo.local:8443');
        $this->options->setToken('secret-token');
        $this->configFactory = new ContextConfigFactory($this->options);
        $this->subject = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
            Psr17FactoryDiscovery::findUriFactory(),
            $this->configFactory
        );
    }

    public function testItThrowsRuntimeExceptionIfTheActionIsNotRecognized(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The action "bar" has no recognized HTTP method.');
        $this->subject->makeRequest('/foo', 'bar');
    }

    public function testItAddsTheTokenToTheHeader(): void
    {
        $request = $this->subject->makeRequest('/foo', 'get');

        $this->assertEquals(['Bearer secret-token'], $request->getHeader('Authorization'));
    }

    public function testItAddsTheBasicAuthToTheHeader(): void
    {
        $this->options->setAuthType(Options::AUTH_TYPE_BASIC);
        $this->options->setUsername('foo');
        $this->options->setPassword('bar');
        $request = $this->subject->makeRequest('/foo', 'get');

        $this->assertEquals(['Basic Zm9vOmJhcg=='], $request->getHeader('Authorization'));
    }

    public function testItAddsTheBodyToTheRequestIfSupplied(): void
    {
        $response = $this->subject->makeRequest('/foo', 'get', null, '{}');

        $this->assertEquals('{}', (string)$response->getBody());
    }

    public function testItAddsTheAcceptHeaderIfSupplied(): void
    {
        $response = $this->subject->makeRequest('/foo', 'get', 'application/json');

        $this->assertEquals(['application/json'], $response->getHeader('Accept'));
    }

    public function testItSetsTheRightPathAndMethodForGet(): void
    {
        $response = $this->subject->makeRequest('/foo', 'get');

        $this->assertEquals('GET', $response->getMethod());
        $this->assertEquals('/foo', (string)$response->getUri());
    }

    public function testItSetsTheRightPathAndMethodForPost(): void
    {
        $response = $this->subject->makeRequest('/foo', 'post');

        $this->assertEquals('POST', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForList(): void
    {
        $response = $this->subject->makeRequest('/foo', 'list');

        $this->assertEquals('GET', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForWatch(): void
    {
        $response = $this->subject->makeRequest('/foo', 'watch');

        $this->assertEquals('GET', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForDelete(): void
    {
        $response = $this->subject->makeRequest('/foo', 'delete');

        $this->assertEquals('DELETE', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForPatch(): void
    {
        $response = $this->subject->makeRequest('/foo', 'patch');

        $this->assertEquals('PATCH', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForPut(): void
    {
        $response = $this->subject->makeRequest('/foo', 'put');

        $this->assertEquals('PUT', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForConnect(): void
    {
        $response = $this->subject->makeRequest('/foo', 'connect');

        $this->assertEquals('GET', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForDeleteCollection(): void
    {
        $response = $this->subject->makeRequest('/foo', 'deletecollection');

        $this->assertEquals('DELETE', $response->getMethod());
    }

    public function testItSetsTheRightPathAndMethodForWatchList(): void
    {
        $response = $this->subject->makeRequest('/foo', 'watchlist');

        $this->assertEquals('GET', $response->getMethod());
    }

    public function testItSetsTheRightMethodWhenPassedExplicitly(): void
    {
        $response = $this->subject->makeRequest(
            '/foo',
            'foo',
            null,
            null,
            null,
            'get'
        );

        $this->assertEquals('GET', $response->getMethod());
    }

    public function testItCanMakeFromRequest(): void
    {
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $result = $this->subject->makeFromRequest(
            '/foo',
            $requestFactory->createRequest('GET', '/bar')
        );

        $this->assertEquals('/foo', (string)$result->getUri());
    }
}
