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
use K8s\Client\KubeConfig\Model\User;
use Symfony\Component\Yaml\Yaml;

class KubeConfigParser
{
    /**
     * Parses the string kubeconfig and returns it as an object.
     *
     * @param string $config The string contents of a ~/.kube/config
     */
    public function parse(string $config): KubeConfig
    {
        $data = Yaml::parse($config);

        $clusters = [];
        if (isset($data['clusters'])) {
            $clusters = $this->parseClusters($data['clusters']);
            unset($data['clusters']);
        }

        $contexts = [];
        if (isset($data['contexts'])) {
            $contexts = $this->parseContexts($data['contexts']);
            unset($data['contexts']);
        }

        $users = [];
        if (isset($data['users'])) {
            $users = $this->parseUsers($data['users']);
            unset($data['users']);
        }

        return new KubeConfig(
            $data,
            $clusters,
            $contexts,
            $users
        );
    }

    /**
     * @return array<int, Cluster>
     */
    private function parseClusters(array $clustersYaml): array
    {
        $clusters = [];

        foreach ($clustersYaml as $clusterYaml) {
            $clusters[] = new Cluster($clusterYaml);
        }

        return $clusters;
    }

    /**
     * @return array<int, Context>
     */
    private function parseContexts(array $contextsYaml): array
    {
        $contexts = [];

        foreach ($contextsYaml as $contextYaml) {
            $contexts[] = new Context($contextYaml);
        }

        return $contexts;
    }

    /**
     * @return array<int, User>
     */
    private function parseUsers(array $usersYaml): array
    {
        $users = [];

        foreach ($usersYaml as $userYaml) {
            $users[] = new User($userYaml);
        }

        return $users;
    }
}
