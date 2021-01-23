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

namespace K8s\Client\Patch;

use K8s\Client\Patch\Contract\OperationInterface;
use K8s\Client\Patch\Operation\Add;
use K8s\Client\Patch\Operation\Copy;
use K8s\Client\Patch\Operation\Move;
use K8s\Client\Patch\Operation\Remove;
use K8s\Client\Patch\Operation\Replace;
use K8s\Client\Patch\Operation\Test;
use K8s\Core\PatchInterface;

class JsonPatch implements PatchInterface
{
    /**
     * @var OperationInterface[]
     */
    private $operations;

    /**
     * @param OperationInterface[] $operations
     */
    public function __construct(array $operations = [])
    {
        $this->operations = $operations;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_map(function (OperationInterface $operation) {
            return $operation->toArray();
        }, $this->operations);
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/json-patch+json';
    }

    /**
     * @return OperationInterface[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param mixed $value
     */
    public function add(string $path, $value): self
    {
        $this->operations[] = new Add($path, $value);

        return $this;
    }

    public function remove(string $path): self
    {
        $this->operations[] = new Remove($path);

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function replace(string $path, $value): self
    {
        $this->operations[] = new Replace($path, $value);

        return $this;
    }

    public function copy(string $from, string $to): self
    {
        $this->operations[] = new Copy($from, $to);

        return $this;
    }

    public function move(string $from, string $to): self
    {
        $this->operations[] = new Move($from, $to);

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function test(string $path, $value): self
    {
        $this->operations[] = new Test($path, $value);

        return $this;
    }
}
