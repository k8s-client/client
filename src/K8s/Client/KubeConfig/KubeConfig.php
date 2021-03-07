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

use K8s\Client\KubeConfig\Model\Cluster;
use K8s\Client\KubeConfig\Model\Context;
use K8s\Client\KubeConfig\Model\FullContext;
use K8s\Client\KubeConfig\Model\User;

class KubeConfig
{
    public const AUTH_TYPE_CERT = 'cert';

    public const AUTH_TYPE_BASIC = 'basic';

    public const AUTH_TYPE_TOKEN = 'token';
    /**
     * @var array<int, Cluster>
     */
    private $clusters;

    /**
     * @var array<int, Context>
     */
    private $contexts;

    /**
     * @var array<int, User>
     */
    private $users;

    /**
     * @var array<string, mixed>
     */
    private $config;

    /**
     * @param array<string, mixed> $config
     * @param array<int, Cluster> $clusters
     * @param array<int, Context> $contexts
     * @param array<int, User> $users
     */
    public function __construct(
        array $config,
        array $clusters,
        array $contexts,
        array $users
    ) {
        $this->config = $config;
        $this->clusters = $clusters;
        $this->contexts = $contexts;
        $this->users = $users;
    }

    public function getCurrentContext(): ?string
    {
        return $this->config['current-context'] ?? null;
    }

    /**
     * @return Cluster[]
     */
    public function getClusters(): array
    {
        return $this->clusters;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return Context[]
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }

    public function getFullContext(?string $name = null): FullContext
    {
        $contextName = $name ?? $this->getCurrentContext();
        if ($contextName === null) {
            throw new \RuntimeException('The current-context is not set. You must be a context name.');
        }
        $context = $this->getContext($contextName);

        return new FullContext(
            $context,
            $this->getCluster($context->getClusterName()),
            $this->getUser($context->getUserName())
        );
    }

    public function getContext(string $name): Context
    {
        $foundContext = null;
        foreach ($this->contexts as $context) {
            if ($context->getName() === $name) {
                $foundContext = $context;
                break;
            }
        }

        if (!$foundContext) {
            throw new \RuntimeException(sprintf(
                'Context %s was not found.',
                $name
            ));
        }

        return $foundContext;
    }

    public function getCluster(string $name): Cluster
    {
        $foundCluster = null;

        foreach ($this->clusters as $cluster) {
            if ($cluster->getName() === $name) {
                $foundCluster = $cluster;
                break;
            }
        }

        if (!$foundCluster) {
            throw new \RuntimeException(sprintf(
                'Context %s was not found.',
                $name
            ));
        }

        return $foundCluster;
    }

    public function getUser(string $name): User
    {
        $foundUser = null;

        foreach ($this->users as $user) {
            if ($user->getName() === $name) {
                $foundUser = $user;
                break;
            }
        }

        if (!$foundUser) {
            throw new \RuntimeException(sprintf(
                'User %s was not found.',
                $name
            ));
        }

        return $foundUser;
    }
}
