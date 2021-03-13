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

namespace unit\K8s\Client;

use K8s\Client\ContextConfig;
use K8s\Client\KubeConfig\Model\FullContext;
use K8s\Client\Options;

class ContextConfigTest extends TestCase
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var FullContext|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $context;

    /**
     * @var ContextConfig
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->options = new Options('foo', 'stuff');
        $this->context = \Mockery::spy(FullContext::class);
        $this->subject = new ContextConfig(
            $this->options,
            $this->context
        );
    }

    public function testGetAuthTypeWithOptions(): void
    {
        $this->subject = new ContextConfig(
            $this->options,
            null
        );

        $this->assertEquals('token', $this->subject->getAuthType());
    }

    public function testGetAuthTypeWithoutOptions(): void
    {
        $this->context->shouldReceive('getAuthType')
            ->andReturn('certificate');

        $this->assertEquals('certificate', $this->subject->getAuthType());
    }

    public function testGetTokenWithOptions(): void
    {
        $this->options->setToken('foo');
        $this->subject = new ContextConfig(
            $this->options,
            null
        );

        $this->assertEquals('foo', $this->subject->getToken());
    }

    public function testGetTokenWithoutOptions(): void
    {
        $this->context->shouldReceive('getUserToken')
            ->andReturn('bar');

        $this->assertEquals('bar', $this->subject->getToken());
    }


    public function testGetNamespaceWithOptions(): void
    {
        $this->subject = new ContextConfig(
            $this->options,
            null
        );

        $this->assertEquals('stuff', $this->subject->getNamespace());
    }

    public function testGetNamespaceWithoutOptions(): void
    {
        $this->context->shouldReceive('getNamespace')
            ->andReturn('default');

        $this->assertEquals('default', $this->subject->getNamespace());
    }

    public function testGetUsernameWithOptions(): void
    {
        $this->options->setUsername('meh');
        $this->subject = new ContextConfig(
            $this->options,
            null
        );

        $this->assertEquals('meh', $this->subject->getUsername());
    }

    public function testGetUsernameWithoutOptions(): void
    {
        $this->context->shouldReceive('getUserUsername')
            ->andReturn('foo');

        $this->assertEquals('foo', $this->subject->getUsername());
    }

    public function testGetPasswordWithOptions(): void
    {
        $this->options->setPassword('stuff');
        $this->subject = new ContextConfig(
            $this->options,
            null
        );

        $this->assertEquals('stuff', $this->subject->getPassword());
    }

    public function testGetPasswordWithoutOptions(): void
    {
        $this->context->shouldReceive('getUserPassword')
            ->andReturn('secret');

        $this->assertEquals('secret', $this->subject->getPassword());
    }

    public function testGetServerWithOptions(): void
    {
        $this->options->setEndpoint('huh');
        $this->subject = new ContextConfig(
            $this->options,
            null
        );

        $this->assertEquals('huh', $this->subject->getServer());
    }

    public function testGetServerWithoutOptions(): void
    {
        $this->context->shouldReceive('getServer')
            ->andReturn('k8s');

        $this->assertEquals('k8s', $this->subject->getServer());
    }

    public function testGetClientCertificate(): void
    {
        $this->context->shouldReceive('getUserClientCertificate')
            ->andReturn('cert');

        $this->assertEquals('cert', $this->subject->getClientCertificate());
    }

    public function testGetClientKey(): void
    {
        $this->context->shouldReceive('getUserClientKey')
            ->andReturn('key');

        $this->assertEquals('key', $this->subject->getClientKey());
    }

    public function testGetClientCertificateData(): void
    {
        $this->context->shouldReceive('getUserClientCertificateData')
            ->andReturn('cert-data');

        $this->assertEquals('cert-data', $this->subject->getClientCertificateData());
    }

    public function testGetClientKeyData(): void
    {
        $this->context->shouldReceive('getUserClientKeyData')
            ->andReturn('key-data');

        $this->assertEquals('key-data', $this->subject->getClientKeyData());
    }

    public function testGetServerCertificateAuthority(): void
    {
        $this->context->shouldReceive('getServerCertificateAuthority')
            ->andReturn('ca');

        $this->assertEquals('ca', $this->subject->getServerCertificateAuthority());
    }

    public function testGetServerCertificateAuthorityData(): void
    {
        $this->context->shouldReceive('getServerCertificateAuthorityData')
            ->andReturn('ca-data');

        $this->assertEquals('ca-data', $this->subject->getServerCertificateAuthorityData());
    }
}
