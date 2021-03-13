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

namespace K8s\Client\KubeConfig;

use K8s\Client\ContextConfig;
use K8s\Client\Options;
use K8s\Core\Contract\ContextConfigInterface;

class ContextConfigFactory
{
    /**
     * @var Options
     */
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function makeContextConfig(): ContextConfigInterface
    {
        return new ContextConfig(
            $this->options,
            $this->options->getKubeConfigContext()
        );
    }
}
