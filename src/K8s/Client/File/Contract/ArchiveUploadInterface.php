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

use K8s\Client\File\Exception\FileException;
use Psr\Http\Message\StreamInterface;

interface ArchiveUploadInterface
{
    /**
     * The argv commands used to perform the upload to the pod of the archive.
     *
     * @return string[]
     */
    public function getUploadCommand(): array;

    /**
     * Return the archive as a PSR-7 stream.
     *
     * @return StreamInterface
     * @throws FileException
     */
    public function toStream(): StreamInterface;
}
