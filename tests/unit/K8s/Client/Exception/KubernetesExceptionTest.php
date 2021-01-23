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

namespace unit\K8s\Client\Exception;

use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;
use K8s\Client\Exception\KubernetesException;
use unit\K8s\Client\TestCase;

class KubernetesExceptionTest extends TestCase
{
    /**
     * @var Status
     */
    private $status;

    /**
     * @var KubernetesException
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->status = new Status();
        $this->status->setMessage('Fail.');
        $this->status->setCode(400);
        $this->subject = new KubernetesException($this->status);
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('Fail.', $this->subject->getMessage());
    }

    public function testGetCode(): void
    {
        $this->assertEquals(400, $this->subject->getCode());
    }

    public function testItCanGetTheStatus(): void
    {
        $this->assertEquals($this->status, $this->subject->getStatus());
    }
}
