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

namespace unit\K8s\Client\KubeConfig\Model;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\KubeConfig\Model\User;
use K8s\Client\KubeConfig\Model\UserExec;
use unit\K8s\Client\TestCase;

class UserTest extends TestCase
{
    /**
     * @var User
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'client-certificate-data' => 'cert-data',
                'client-certificate' => 'cert',
                'client-key' => 'key',
                'client-key-data' => 'key-data',
                'token' => 'token',
                'username' => 'user',
                'password' => 'pass',
                'token-file' => '/yay',
                'exec' => ['command' => 'get-token'],
            ]
        ]);
    }

    public function testGetClientCertificate(): void
    {
        $this->assertEquals('cert', $this->subject->getClientCertificate());
    }

    public function testGetClientCertificateData(): void
    {
        $this->assertEquals('cert-data', $this->subject->getClientCertificateData());
    }

    public function testGetClientKey(): void
    {
        $this->assertEquals('key', $this->subject->getClientKey());
    }

    public function testGetClientKeyData(): void
    {
        $this->assertEquals('key-data', $this->subject->getClientKeyData());
    }

    public function testGetToken(): void
    {
        $this->assertEquals('token', $this->subject->getToken());
    }

    public function testGetUsername(): void
    {
        $this->assertEquals('user', $this->subject->getUsername());
    }

    public function testGetPassword(): void
    {
        $this->assertEquals('pass', $this->subject->getPassword());
    }

    public function testGetExec(): void
    {
        $this->assertInstanceOf(UserExec::class, $this->subject->getExec());
    }

    public function testGetAuthTypeForCertWithKey(): void
    {
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'client-key' => 'key',
            ]
        ]);

        $this->assertEquals('certificate', $this->subject->getAuthType());
    }

    public function testGetAuthTypeForCertWithKeyData(): void
    {
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'client-key-data' => 'key',
            ]
        ]);

        $this->assertEquals('certificate', $this->subject->getAuthType());
    }

    public function testGetAuthTypeForExec(): void
    {
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'exec' => ['command' => 'foo'],
            ]
        ]);

        $this->assertEquals('token', $this->subject->getAuthType());
    }

    public function testGetAuthTypeForToken(): void
    {
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'token' => 'foo',
            ]
        ]);

        $this->assertEquals('token', $this->subject->getAuthType());
    }

    public function testGetAuthTypeForTokenFromFile(): void
    {
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'token-file' => 'foo',
            ]
        ]);

        $this->assertEquals('token', $this->subject->getAuthType());
    }

    public function testGetAuthTypeForBasic(): void
    {
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'username' => 'foo',
                'password' => 'bar',
            ]
        ]);

        $this->assertEquals('basic', $this->subject->getAuthType());
    }

    public function testGetAuthTypeWhenNoneIsSupported(): void
    {
        $this->subject = new User([
            'name' => 'foo',
            'user' => [
                'foo' => 'bar'
            ]
        ]);

        $this->expectException(RuntimeException::class);
        $this->subject->getAuthType();
    }
}
