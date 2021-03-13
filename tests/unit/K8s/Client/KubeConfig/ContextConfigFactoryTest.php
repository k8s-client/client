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

namespace unit\K8s\Client\KubeConfig;

use K8s\Client\KubeConfig\ContextConfigFactory;
use K8s\Client\Options;
use K8s\Core\Contract\ContextConfigInterface;
use unit\K8s\Client\TestCase;

class ContextConfigFactoryTest extends TestCase
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var ContextConfigFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->options = new Options('foo');
        $this->subject = new ContextConfigFactory(
            $this->options
        );
    }

    public function testItMakesTheContextConfig(): void
    {
        $result = $this->subject->makeContextConfig();

        $this->assertInstanceOf(ContextConfigInterface::class, $result);
    }
}
