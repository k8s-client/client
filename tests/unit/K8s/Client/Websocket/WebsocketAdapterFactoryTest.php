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

use K8s\Client\Exception\RuntimeException;
use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Client\Options;
use K8s\Client\Websocket\WebsocketAdapterFactory;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use unit\K8s\Client\TestCase;

class WebsocketAdapterFactoryTest extends TestCase
{
    /**
     * @var WebsocketAdapterFactory
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
        $this->options = new Options('foo');
        $this->configFactory = new ContextConfigFactory($this->options);
        $this->subject = new WebsocketAdapterFactory(
            $this->options,
            $this->configFactory
        );
    }

    public function testMakeClientWithSpecificAdapter(): void
    {
        $client = \Mockery::spy(WebsocketClientInterface::class);
        $this->options->setWebsocketClient($client);

        $result = $this->subject->makeAdapter();
        $this->assertEquals($client, $result);
    }

    public function testMakeAdapterWithNoAdapter(): void
    {
        $this->subject = new WebsocketAdapterFactory(
            $this->options,
            $this->configFactory,
            []
        );

        $this->expectException(RuntimeException::class);
        $this->subject->makeAdapter();
    }
}
