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

namespace unit\K8s\Client\Http\Exception;

use K8s\Client\Http\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use unit\K8s\Client\TestCase;

class HttpExceptionTest extends TestCase
{
    private $response;

    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->response = \Mockery::spy(ResponseInterface::class);
        $this->response->shouldReceive([
           'getStatusCode' => 500,
           'getReasonPhrase' => 'oops',
        ]);
        $this->subject = new HttpException($this->response);
    }

    public function testGetCode(): void
    {
        $this->assertEquals(500, $this->subject->getCode());
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('oops', $this->subject->getMessage());
    }

    public function testGetResponse(): void
    {
        $this->assertEquals($this->response, $this->subject->getResponse());
    }
}
