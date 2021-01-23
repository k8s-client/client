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

namespace K8s\Client\Patch\Operation;

class Copy extends AbstractOperation
{
    /**
     * @var string
     */
    private $from;

    public function __construct(string $from, string $path)
    {
        $this->from = $from;
        parent::__construct(
            'copy',
            $path
        );
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return $this
     */
    public function setFrom(string $from)
    {
        $this->from = $from;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            ['from' => $this->from]
        );
    }
}
