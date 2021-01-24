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

namespace unit\K8s\Client\Websocket;

use K8s\Client\Websocket\ExecConnection;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use unit\K8s\Client\TestCase;

class ExecConnectionTest extends TestCase
{
    /**
     * @var WebsocketConnectionInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $connection;

    /**
     * @var ExecConnection
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = \Mockery::spy(WebsocketConnectionInterface::class);
        $this->subject = new ExecConnection($this->connection);
    }

    public function testWrite(): void
    {
        $this->connection->shouldReceive('send')
            ->with("\x00foo");

        $this->subject->write('foo');
    }

    public function testWriteln(): void
    {
        $this->connection->shouldReceive('send')
            ->with("\x00foo\n");

        $this->subject->writeln('foo');
    }

    public function testMultiWriteln(): void
    {
        $this->connection->shouldReceive('send')
            ->with("\x00foo\n");
        $this->connection->shouldReceive('send')
            ->with("\x00bar\n");

        $this->subject->writeln(['foo', 'bar']);
    }

    public function testKeepAlive(): void
    {
        $this->connection->shouldReceive('send')
            ->with("\x00");

        $this->subject->keepalive();
    }

    public function testClose(): void
    {
        $this->subject->close();
        $this->connection->shouldHaveReceived('close');
    }
}
