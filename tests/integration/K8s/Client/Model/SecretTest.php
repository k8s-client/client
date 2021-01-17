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

namespace integration\K8s\Client\Model;

use integration\K8s\Client\TestCase;
use K8s\Api\Model\Api\Core\v1\Secret;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;

class SecretTest extends TestCase
{
    public function testItCanCreateTheSecret(): void
    {
        $secret = new Secret('very-secret');
        $secret->setData([
            'super-secret' => base64_encode('dont tell anyone'),
        ]);

        $created = $this->k8s()->create($secret);
        $this->assertEquals('very-secret', $created->getName());
        $this->assertEquals(base64_decode($secret->getData()['super-secret']), 'dont tell anyone');
    }

    public function testItCanReadTheSecret(): void
    {
        $secret = $this->k8s()->read('very-secret', Secret::class);

        $this->assertEquals('very-secret', $secret->getName());
    }

    public function testItCanDeleteTheSecret(): void
    {
        $secret = $this->k8s()->read('very-secret', Secret::class);
        $status = $this->k8s()->delete($secret);

        $this->assertInstanceOf(Status::class, $status);
    }
}
