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

namespace K8s\Client\File;

use K8s\Client\File\Exception\FileException;

class FileResource
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var resource|null
     */
    private $resource;

    /**
     * @param resource|null $resource
     */
    public function __construct(string $file, $resource = null)
    {
        $this->file = $file;
        $this->resource = $resource;
    }

    public function write(string $data): void
    {
        $result = @fwrite($this->resource(), $data);
        if ($result === false) {
            throw new FileException(sprintf(
                'Unable to write to file: %s',
                $this->file
            ));
        }
    }

    public function close(): void
    {
        if (!@fclose($this->resource())) {
            throw new FileException(sprintf(
                'Unable to close file: %s',
                $this->file
            ));
        }
        $this->resource = null;
    }

    public function delete(): void
    {
        if (!file_exists($this->file)) {
            return;
        }
        if ($this->resource) {
            $this->close();
        }
        if (!@unlink($this->file)) {
            throw new FileException(sprintf(
                'Unable to delete file: %s',
                $this->file
            ));
        }
    }

    public function getPath(): string
    {
        return $this->file;
    }

    /**
     * @return resource
     */
    private function resource()
    {
        if ($this->resource) {
            return $this->resource;
        }
        $this->resource = @fopen($this->file, 'w');
        if ($this->resource === false) {
            throw new FileException(sprintf(
                'Unable to open file for writing: %s',
                $this->file
            ));
        }

        return $this->resource;
    }
}
