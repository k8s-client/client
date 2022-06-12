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

namespace K8s\Client;

use K8s\Client\Exception\RuntimeException;
use K8s\Client\KubeConfig\KubeConfigParser;
use K8s\Client\KubeConfig\Model\FullContext;
use K8s\Core\Contract\HttpClientFactoryInterface;
use K8s\Core\Contract\WebsocketClientFactoryInterface;

class K8sFactory
{
    private const WEBSOCKET_FACTORIES = [
        'K8s\WsSwoole\AdapterFactory',
        'K8s\WsRatchet\AdapterFactory',
    ];

    private const HTTPCLIENT_FACTORIES = [
        'K8s\HttpSymfony\ClientFactory',
        'K8s\HttpGuzzle\ClientFactory',
    ];

    private const KUBE_CONFIG_PATH =  DIRECTORY_SEPARATOR . '.kube' . DIRECTORY_SEPARATOR . 'config';

    /**
     * @var KubeConfigParser
     */
    private $kubeConfigParser;

    /**
     * @var HttpClientFactoryInterface|null
     */
    private $httpClientFactory;

    /**
     * @var WebsocketClientFactoryInterface|null
     */
    private $websocketClientFactory;

    public function __construct(
        ?KubeConfigParser $kubeConfigParser = null,
        ?HttpClientFactoryInterface $httpClientFactory = null,
        ?WebsocketClientFactoryInterface $websocketClientFactory = null
    ) {
        $this->kubeConfigParser = $kubeConfigParser ?? new KubeConfigParser();
        $this->websocketClientFactory = $websocketClientFactory;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * Set a HttpClientFactory to use when instantiating the K8s client.
     *
     * @param HttpClientFactoryInterface $httpClientFactory
     * @return $this
     */
    public function usingHttpClientFactory(HttpClientFactoryInterface $httpClientFactory): self
    {
        $this->httpClientFactory = $httpClientFactory;

        return $this;
    }

    /**
     * Set a WebsocketClientFactory to use when instantiating the K8s client.
     *
     * @param WebsocketClientFactoryInterface $websocketClientFactory
     * @return $this
     */
    public function usingWebsocketClientFactory(WebsocketClientFactoryInterface $websocketClientFactory): self
    {
        $this->websocketClientFactory = $websocketClientFactory;

        return $this;
    }

    /**
     * Load the k8s client from the default KubeConfig file. It will load it as follows:
     *
     *   1. If the second parameter for a file path is specified, it will use that.
     *   2. Attempt to load from the current working directory: $CWD/.kube/config).
     *   3. Attempt to load from the default home location: $HOME/.kube/config).
     *
     * @param string|null $contextName A specific context name to use from the config.
     * @param string|null $configFilePath A specific kubeconfig file path. If not provided, it will attempt to find one.
     * @return K8s
     */
    public function loadFromKubeConfig(
        ?string $contextName = null,
        ?string $configFilePath = null
    ): K8s {
        return $this->loadFromKubeConfigData(
            $this->getKubeConfigContents($configFilePath),
            $contextName
        );
    }

    /**
     * Convenience method for loading the config from a specific file path.
     *
     * @param string $configFilePath The full file path to load the kubeconfig from.
     * @param string|null $contextName A specific context name to use from the config.
     * @return K8s
     */
    public function loadFromKubeConfigFile(
        string $configFilePath,
        ?string $contextName = null
    ): K8s {
        return $this->loadFromKubeConfig(
            $contextName,
            $configFilePath
        );
    }

    /**
     * Load the k8s client from any raw YAML string of a KubeConfig file.
     *
     * @param string $kubeConfig The raw YAML string from a KubeConfig file.
     * @param string|null $contextName A specific context name to use from the config.
     * @return K8s
     */
    public function loadFromKubeConfigData(
        string $kubeConfig,
        ?string $contextName = null
    ): K8s {
        $kubeConfig = $this->kubeConfigParser->parse($kubeConfig);
        $context = $kubeConfig->getFullContext($contextName);

        return $this->loadFromKubeConfigContext(
            $context
        );
    }

    /**
     * Load the k8s client from a pre-processed KubeConfig context.
     *
     * @param FullContext $context the full context from the parsed kubeconfig.
     * @return K8s
     */
    private function loadFromKubeConfigContext(
        FullContext $context
    ): K8s {
        $options = new Options(
            $context->getServer(),
            $context->getNamespace() ?? 'default'
        );
        $options->setKubeConfigContext($context);

        $httpClientFactory = $this->httpClientFactory;
        if (!$httpClientFactory) {
            foreach (self::HTTPCLIENT_FACTORIES as $clientFactory) {
                if (class_exists($clientFactory)) {
                    $httpClientFactory = new $clientFactory();
                }
            }
        }
        if ($httpClientFactory) {
            $options->setHttpClientFactory($httpClientFactory);
        }
        $websocketClientFactory = $this->websocketClientFactory;
        if (!$websocketClientFactory) {
            foreach (self::WEBSOCKET_FACTORIES as $clientFactory) {
                if (class_exists($clientFactory)) {
                    $websocketClientFactory = new $clientFactory();
                }
            }
        }
        if ($websocketClientFactory) {
            $options->setWebsocketClientFactory($websocketClientFactory);
        }

        return new K8s($options);
    }

    private function getKubeConfigContents(?string $configFilePath = null): string
    {
        $defaultConfig = getcwd() . self::KUBE_CONFIG_PATH;
        $homeConfig = ($_SERVER['HOME'] ?? '') . self::KUBE_CONFIG_PATH;

        if ($configFilePath !== null && !file_exists($configFilePath)) {
            throw new RuntimeException(sprintf(
                'The specified kubeconfig file was not found: %s',
                $configFilePath
            ));
        }

        if ($configFilePath !== null) {
            $configContents = (string)file_get_contents($configFilePath);
        } elseif (file_exists($defaultConfig)) {
            $configContents = (string)file_get_contents($defaultConfig);
        } elseif (file_exists($homeConfig)) {
            $configContents = (string)file_get_contents($homeConfig);
        } else {
            throw new RuntimeException(sprintf(
                'A kubeconfig file was not found. Checked these paths: %s, %s',
                $defaultConfig,
                $homeConfig
            ));
        }

        if ($configContents === '') {
            throw new RuntimeException('The kubeconfig file is empty.');
        }

        return $configContents;
    }
}
