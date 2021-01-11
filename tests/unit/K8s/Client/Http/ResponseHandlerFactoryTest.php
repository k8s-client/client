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

use K8s\Client\Http\Contract\ResponseHandlerInterface;
use K8s\Client\Http\ResponseHandlerFactory;
use K8s\Client\Serialization\Serializer;
use unit\K8s\Client\TestCase;

class ResponseHandlerFactoryTest extends TestCase
{
    /**
     * @var ResponseHandlerFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new ResponseHandlerFactory();
    }

    public function testItReturnsResponseHandlers(): void
    {
        $result = $this->subject->makeHandlers(\Mockery::spy(Serializer::class));

        foreach ($result as $handler) {
            $this->assertInstanceOf(ResponseHandlerInterface::class, $handler);
        }

        $this->assertNotEmpty($result);
    }
}
