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

namespace K8s\Client\File\Contract;

interface ArchiveInterface extends ArchiveUploadInterface
{
    public function delete(): void;

    public function addFile(string $source, string $destination): void;

    public function addFromString(string $destination, string $data): void;
}
