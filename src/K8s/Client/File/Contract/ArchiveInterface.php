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

interface ArchiveInterface extends ArchiveUploadInterface
{
    /**
     * Get the full path to the archive.
     *
     * @return string
     */
    public function getRealPath(): string;

    /**
     * Extract the archive to a specific location.
     *
     * @param string $path the path to extract to.
     * @param null|array|string $files The string file, or array of files, to extract. Defaults to all if not set.
     * @param bool $overwrite Whether or not to overwrite existing files.
     * @throws FileException
     */
    public function extractTo(string $path, $files = null, bool $overwrite = false): void;

    /**
     * Delete the archive.
     *
     * @throws FileException
     */
    public function delete(): void;

    /**
     * Add a file to the archive.
     *
     * @param string $source The source file.
     * @param string $destination The destination in the archive.
     */
    public function addFile(string $source, string $destination): void;

    /**
     * Add a file to the archive from string data.
     *
     * @param string $destination The destination in the archive.
     * @param string $data The string data.
     */
    public function addFromString(string $destination, string $data): void;
}
