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

namespace K8s\Client\Http;

use K8s\Client\Exception\InvalidArgumentException;
use K8s\Client\Options;

class UriBuilder
{
    /**
     * @var Options
     */
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function buildUri(string $uri, array $parameters = [], array $query = [], ?string $namespace = null): string
    {
        $namespace = $namespace ?? $this->options->getNamespace();
        $parameters['{namespace}'] = $namespace;
        $uri = str_replace(
            array_keys($parameters),
            array_values($parameters),
            $uri
        );

        if (preg_match('/{(.*)}/', $uri, $matches)) {
            $parameterName = $matches[1];

            throw new InvalidArgumentException(sprintf(
                'The parameter %s is required.',
                $parameterName
            ));
        }

        $uri = $this->options->getEndpoint() . $uri;
        if (!empty($query)) {
            $uri .= $this->buildQueryString($query);
        }

        return $uri;
    }

    private function buildQueryString(array $query): string
    {
        $arrayParams = [];
        foreach ($query as $item => $value) {
            if (is_array($value)) {
                $arrayParams[$item] = $value;
            }
        }

        $additional = [];
        if (!empty($arrayParams)) {
            foreach ($arrayParams as $key => $values) {
                unset($query[$key]);
                foreach ($values as $value) {
                    $additional[] = urlencode($key) . '=' . urlencode((string)$value);
                }
            }
        }

        $additional = implode('&', $additional);
        if (!empty($additional)) {
            $additional = empty($query) ? '?' . $additional : '&' . $additional;
        }

        $query = empty($query) ? $additional : http_build_query($query) . $additional;
        if (!empty($query) && $query[0] !== '?') {
            $query = '?' . $query;
        }

        return $query;
    }
}
