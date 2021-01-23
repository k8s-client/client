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

use K8s\Client\Patch\Contract\OperationInterface;

abstract class AbstractOperation implements OperationInterface
{
    /**
     * @var string
     */
    protected $opName;

    /**
     * @var string
     */
    protected $path;

    public function __construct(string $opName, string $path)
    {
        $this->opName = $opName;
        $this->path = $path;
    }

    public function getOp(): string
    {
        return $this->opName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'op' => $this->opName,
            'path' => $this->path,
        ];
    }
}
