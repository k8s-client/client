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

class Move extends AbstractOperation
{
    /**
     * @var string
     */
    private $from;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        parent::__construct(
            'move',
            $to
        );
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            ['from' => $this->from]
        );
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }
}
