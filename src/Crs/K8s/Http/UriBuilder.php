<?php

/**
 * This file is part of the crs/k8s library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crs\K8s\Http;

use Crs\K8s\Exception\InvalidArgumentException;
use Crs\K8s\Options;

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

    /**
     * @param array|object $query
     */
    public function buildUri(string $uri, array $parameters, $query, ?string $namespace): string
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
            $uri .= '?' . http_build_query($query);
        }

        return $uri;
    }
}
